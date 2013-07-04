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
        $migratedIds = array();
        $migratedNames = array();
        $output->writeln('Start migration');
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        if ($input->getOption('clean')) {
            $entityManager->getConnection()->exec('DELETE from customer_contact');
            $output->writeln('Deleted contacts');
            $entityManager->getConnection()->exec('DELETE from customer');
            $output->writeln('Deleted customers');
        }

        $qfactDb = new \PDO('mysql:host=localhost;dbname=qfact_migration', 'root', '');
        $stmt = $qfactDb->prepare('SELECT * from 2013_clients');
        $stmt->execute();
        $results = $stmt->fetchAll();

        foreach ($results as $result) {
            $result['nom'] =  mb_convert_encoding ($result['nom'], "UTF-8");
            $result['rao_social'] =  mb_convert_encoding ($result['rao_social'], "UTF-8");
            if (!$result['rao_social'] || !$result['cif']) {
                $output->writeln('--- '.$result['nom'].'/'.$result['rao_social'].'/'.$result['cif'].' ... NOT MIGRATED. NO IDENT OR NAME');
            }
            else {
                if (array_search($result['cif'], $migratedIds) === false) {
                    if (array_search($result['rao_social'], $migratedNames) === false) {
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
                        $customer->setCountry($result['pais']);
                        // $customer->setPaymentPeriod();
                        // $customer->setPaymentDay();
                        $entityManager->persist($customer);
                        $migratedIds[] = $result['cif'];
                        $migratedNames[] = $result['rao_social'];
                        $output->writeln('migrated');
                    }
                    else {
                        $output->writeln('--- '.$result['nom'].'/'.$result['rao_social'].'/'.$result['cif'].' ... NOT MIGRATED. ALREADY EXISTS LEGAL NAME.');
                    }
                }
                else
                {
                    $output->writeln('--- '.$result['nom'].'/'.$result['rao_social'].'/'.$result['cif'].' ... NOT MIGRATED. ALREADY EXISTS IDENT.');
                }
            }
        }

        $entityManager->flush();

        $output->writeln('End migration');
    }
}