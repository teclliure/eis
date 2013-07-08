<?php
/*
 * This file is part of Teclliure developed package build on 7/4/13.
 *
 * (c) Marc Montañés Abarca <marc@teclliure.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Teclliure\InvoiceBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Teclliure\CustomerBundle\Entity\Customer;
use Teclliure\CustomerBundle\Entity\Contact;
use Teclliure\InvoiceBundle\Entity\Tax;
use Teclliure\InvoiceBundle\Entity\CommonLine;

class MigrateQfactCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:qfact')
            ->setDescription('Migrate Qfact customer and quote data to EIS')
            ->addOption('clean', null, InputOption::VALUE_OPTIONAL, 'If set, the task will yell in uppercase letters', false)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $quoteService = $this->getContainer()->get('quote_service');
        $migratedIds = array();
        $migratedNames = array();
        $output->writeln('Start migration');
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        if ($input->getOption('clean')) {
            $entityManager->getConnection()->exec('DELETE from customer_contact');
            $output->writeln('Deleted contacts');
            $entityManager->getConnection()->exec('DELETE from customer');
            $output->writeln('Deleted customers');
            $entityManager->getConnection()->exec('DELETE from common_line');
            $entityManager->getConnection()->exec('DELETE from common');
            $output->writeln('Deleted quotes');
            $entityManager->getConnection()->exec('DELETE from tax');
            $output->writeln('Deleted taxes');
        }

        $qfactDb = new \PDO('mysql:host=localhost;dbname=qfact_migration', 'root', '');

        $stmt = $qfactDb->prepare('SELECT * from 2013_tipoiva where empresa=2');
        $stmt->execute();
        $vatTypes = $stmt->fetchAll();

        $taxes = array();
        foreach ($vatTypes as $vatType) {
            $tax = new Tax();
            $tax->setName('Iva '.$vatType['iva']);
            $tax->setValue($vatType['iva']);
            $entityManager->persist($tax);
            $taxes[$vatType['codi']] = $tax;
        }

        $stmt = $qfactDb->prepare('SELECT * from 2013_clients where empresa=2');
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach ($results as $key=>$result) {
            $result = $this->convertEncodingArray($result);
            if (!$result['rao_social']) {
                $result['rao_social'] = 'NoLegalName'.$key;
            }
            if (!$result['cif']) {
                $result['cif'] = 'NoCIF'.$key;
            }
            if (in_array($result['cif'], $migratedIds)) {
                $result['cif'] = 'Duplicated'.$key.'-'.$result['cif'];
            }
            if (in_array(strtolower($result['rao_social']), $migratedNames)) {
                $result['rao_social'] = 'Duplicated'.$key.'-'.$result['rao_social'];
            }
            $output->write('+++ '.$result['nom'].'/'.$result['rao_social'].'/'.$result['cif'].' ... ');
            $customer = new Customer();
            $customer->setName($result['nom']);
            $customer->setLegalName($result['rao_social']);
            $customer->setIdentification($result['cif']);
            $customer->setEmail($result['email']);
            $customer->setPhone($result['telefon']);
            $customer->setWeb($result['url']);
            $customer->setZipCode($result['codpos']);
            $customer->setAddress($result['adressa']);
            $customer->setCity($result['poblacio']);
            $customer->setState($result['provincia']);
            if ($result['pais'] == 'España' || $result['pais'] == 'E') {
                $result['pais'] = 'ES';
            }
            elseif ($result['pais'] == 'Portugal') {
                $result['pais'] = 'PT';
            }
            elseif ($result['pais'] == 'France') {
                $result['pais'] = 'FR';
            }
            elseif ($result['pais'] == 'Italia') {
                $result['pais'] = 'IT';
            }
            elseif ($result['pais'] == 'SEOUL KOREA') {
                $result['pais'] = 'KR';
            }
            $customer->setCountry($result['pais']);
            // $customer->setPaymentPeriod();
            // $customer->setPaymentDay();

            $stmt = $qfactDb->prepare('SELECT * from 2013_clients_contactes where client = '.$result['codi']);
            $stmt->execute();
            $contacts = $stmt->fetchAll();

            foreach ($contacts as $contact) {
                $contact = $this->convertEncodingArray($contact);

                $dbContact = new Contact();
                $dbContact->setName(mb_convert_encoding ($contact['nom'], "UTF-8"));
                $dbContact->setEmail($contact['email']);
                $dbContact->setPhone($contact['telefon']);
                $dbContact->setSendQuote($contact['rep_presupost']);
                $dbContact->setSendDeliveryNote($contact['rep_albara']);
                $dbContact->setSendInvoice($contact['rep_factura']);

                $customer->addContact($dbContact);
            }

            $stmt = $qfactDb->prepare('SELECT * from 2013_c_presupost where client = '.$result['codi']);
            $stmt->execute();
            $pres = $stmt->fetchAll();

            foreach ($pres as $pre) {
                $pre = $this->convertEncodingArray($pre);

                $quote = $quoteService->createQuote();
                $quote->setCustomer($customer);
                $quote->setCustomerName($customer->getLegalName());
                $quote->setCustomerIdentification($customer->getIdentification());
                $quote->setCustomerZipCode($customer->getZipCode());
                $quote->setCustomerAddress($customer->getAddress());
                $quote->setCustomerCity($customer->getCity());
                $quote->setCustomerState($customer->getState());
                $quote->setCustomerCountry($customer->getCountry());
                $quote->setFootnote($pre['text_peu']);
                $quote->getQuote()->setCreated(new \DateTime($pre['data']));

                $stmt = $qfactDb->prepare('SELECT * from 2013_d_presupost where num_pre = '.$pre['num_pre']);
                $stmt->execute();
                $presLines = $stmt->fetchAll();
                foreach ($presLines as $line) {
                    $line = $this->convertEncodingArray($line);

                    $desc = '';
                    $desc .= $line['descripcio']."\n";

                    if ($line['unitats']) {
                        $commonLine = new CommonLine();
                        $commonLine->setDescription($line['descripcio']);
                        $commonLine->addTax($taxes[$line['iva']]);
                        $commonLine->setQuantity($line['unitats']);
                        $commonLine->setUnitaryCost($line['preu']);
                        $quote->addCommonLine($commonLine);
                    }
                }
                $quote->setDescription($desc);

                $quoteService->saveQuote($quote);
            }

            /*$stmt = $qfactDb->prepare('SELECT * from 2013_c_presupost where client = '.$result['codi']);
            $stmt->execute();
            $pres = $stmt->fetchAll();

            $stmt = $qfactDb->prepare('SELECT * from 2013_clients_contactes where client = '.$result['codi']);
            $stmt->execute();
            $contacts = $stmt->fetchAll();*/

            $entityManager->persist($customer);
            $migratedIds[] = $result['cif'];
            $migratedNames[] = strtolower($result['rao_social']);
            $output->writeln('migrated with '.count($contacts).' contacts and '.count($pres).' quotes.');
        }

        $entityManager->flush();

        $output->writeln('End migration');
    }

    protected function convertEncodingArray($array) {
        foreach ($array as $key=>$value) {
            $array[$key] = mb_convert_encoding($value, "UTF-8");
        }
        return $array;
    }
}