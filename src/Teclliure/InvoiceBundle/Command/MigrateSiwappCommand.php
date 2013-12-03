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

class MigrateSiwappCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('migrate:siwapp')
            ->setDescription('Migrate Siwapp customer and invoice data to EIS')
            ->addOption('clean', null, InputOption::VALUE_OPTIONAL, 'If set, the task will yell in uppercase letters', false)
            ->addArgument('siwapp_db_name', InputArgument::REQUIRED, 'The Siwapp db name')
            ->addArgument('siwapp_db_username', InputArgument::REQUIRED, 'The Siwapp db username')
            ->addArgument('siwapp_db_password', InputArgument::REQUIRED, 'The Siwapp db password')
            ->addArgument('siwapp_db_host', InputArgument::REQUIRED, 'The Siwapp db hostname')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $quoteService = $this->getContainer()->get('quote_service');
        $invoiceService = $this->getContainer()->get('invoice_service');
        $migratedIds = array();
        $migratedNames = array();
        $output->writeln('Started migration from Siwapp');
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        if ($input->getOption('clean')) {
            $entityManager->getConnection()->exec('DELETE from tax');
            $output->writeln('Deleted taxes');
            /*    $entityManager->getConnection()->exec('DELETE from customer_contact');
                $output->writeln('Deleted contacts');
                $entityManager->getConnection()->exec('DELETE from customer');
                $output->writeln('Deleted customers');
                $entityManager->getConnection()->exec('DELETE from common_line');
                $entityManager->getConnection()->exec('DELETE from common');
                $output->writeln('Deleted quotes');
            */
        }

        $siwappDb = new \PDO('mysql:host='.$input->getArgument('siwapp_db_host').';dbname='.$input->getArgument('siwapp_db_name'), $input->getArgument('siwapp_db_username'), $input->getArgument('siwapp_db_password'));

        $stmt = $siwappDb->prepare('SELECT * from tax');
        $stmt->execute();
        $vatTypes = $stmt->fetchAll();

        $taxes = array();
        foreach ($vatTypes as $vatType) {
            $tax = new Tax();
            $tax->setName($vatType['name']);
            $tax->setValue($vatType['value']);
            $tax->setActive($vatType['active']);
            $tax->setIsDefault($vatType['is_default']);
            $entityManager->persist($tax);
            $taxes[$vatType['id']] = $tax;
        }

        // TODO: Migrate Series

        $stmt = $siwappDb->prepare('SELECT * from customer');
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach ($results as $key=>$result) {
            $result = $this->convertEncodingArray($result);
            if (!$result['name']) {
                $result['name'] = 'NoLegalName'.$key;
            }
            if (!$result['identification']) {
                $result['identification'] = 'NoIdent'.$key;
            }
            if (in_array($result['identification'], $migratedIds)) {
                $result['identification'] = 'Duplicated'.$key.'-'.$result['identification'];
            }
            if (in_array(strtolower($result['name']), $migratedNames)) {
                $result['name'] = 'Duplicated'.$key.'-'.$result['name'];
            }
            $output->write('+++ '.$result['name'].'/'.$result['identification'].' ... ');
            $customer = new Customer();
            $customer->setName($result['name']);
            $customer->setLegalName($result['name']);
            $customer->setIdentification($result['identification']);
            $customer->setEmail($result['email']);
            $customer->setAddress($result['invoicing_address']);

            $dbContact = new Contact();
            $dbContact->setName(mb_convert_encoding ($result['contact_person'], "UTF-8"));
            $dbContact->setEmail($result['email']);
            $customer->addContact($dbContact);

            $stmt = $siwappDb->prepare('SELECT * from common where customer_id = '.$result['id']);
            $stmt->execute();
            $commons = $stmt->fetchAll();

            foreach ($commons as $common) {
                $common = $this->convertEncodingArray($common);

                if ($common['type'] == 'Estimate') {
                    $newObject = $quoteService->createQuote();
                    /*
                     * Quote - Siwapp
                     * DRAFT    = 0;
                     * REJECTED = 1;
                     * PENDING  = 2;
                     * APPROVED = 3;
                     *
                     * Quote - EIS
                     * DRAFT             - 0
                     * PENDING           - 1
                     * REJECTED          - 2
                     * DELIVERED         - 3
                     * INVOICED          - 4
                     * PARTLYINVOICED    - 5
                     *
                     */
                    if ($common['status'] == 1) {
                        $newObject->setStatus(2);
                    }
                    elseif ($common['status'] == 2) {
                        $newObject->setStatus(1);
                    }
                    elseif ($common['status'] == 3) {
                        $newObject->setStatus(4);
                    }
                    else {
                        $newObject->setStatus($common['status']);
                    }
                }
                elseif ($common['type'] == 'Invoice') {
                    $newObject = $quoteService->createInvoice();
                    /*
                    *  Invoice status - Siwapp
                    *  DRAFT   = 0;
                    *  CLOSED  = 1;
                    *  OPENED  = 2;
                    *  OVERDUE = 3;
                    *
                    *  Invoice status - EIS
                    *  DRAFT         - 0
                    *  CLOSED        - 1
                    *  OVERDUE       - 2
                    *  PAID          - 3
                    */
                    if ($common['status'] == 1) {
                        $newObject->setStatus(3);
                    }
                    elseif ($common['status'] == 2) {
                        $newObject->setStatus(1);
                    }
                    elseif ($common['status'] == 3) {
                        $newObject->setStatus(2);
                    }
                    else {
                        $newObject->setStatus($common['status']);
                    }
                }

                $newObject->getCommon()->setCustomer($customer);
                $newObject->getCommon()->setCustomerName($common['customer_name']);
                $newObject->getCommon()->setCustomerIdentification($common['customer_identification']);
                $newObject->getCommon()->setCustomerAddress($common['invoicing_address']);
                $newObject->setNumber($common['number']);
                $newObject->setFootnote($common['notes']);
                $newObject->setCreated(new \DateTime($common['created_at']));
                $newObject->setUpdated(new \DateTime($common['updated_at']));

                $stmt = $siwappDb->prepare('SELECT * from item where common_id = '.$invoice['id']);
                $stmt->execute();
                $presLines = $stmt->fetchAll();
                $desc = '';
                foreach ($presLines as $line) {
                    $line = $this->convertEncodingArray($line);
                    $desc .= $line['descripcio']."\n";

                    if ($line['unitats'] && $line['preu']) {
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

            /*$stmt = $siwappDb->prepare('SELECT * from 2013_c_presupost where client = '.$result['codi']);
            $stmt->execute();
            $pres = $stmt->fetchAll();

            $stmt = $siwappDb->prepare('SELECT * from 2013_clients_contactes where client = '.$result['codi']);
            $stmt->execute();
            $contacts = $stmt->fetchAll();*/

            $entityManager->persist($customer);
            $migratedIds[] = $result['cif'];
            $migratedNames[] = strtolower($result['rao_social']);
            $output->writeln('migrated with '.count($contacts).' contacts and '.count($pres).' quotes.');
        }

        $entityManager->flush();

        $output->writeln('End siwapp migration');
    }

    protected function convertEncodingArray($array) {
        foreach ($array as $key=>$value) {
            $array[$key] = mb_convert_encoding($value, "UTF-8");
        }
        return $array;
    }
}