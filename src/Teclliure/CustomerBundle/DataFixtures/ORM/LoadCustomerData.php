<?php
/*
 * This file is part of Teclliure developed package build on 5/24/13.
 *
 * (c) Marc Montañés Abarca <marc@teclliure.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teclliure\CustomerBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Teclliure\CustomerBundle\Entity\Customer;

class LoadCustomerData implements FixtureInterface
{
    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $customer = new Customer();
        $customer->setIdentification('123132132321');
        $customer->setActive(1);
        $customer->setLegalName('Active customer 123 SSLL');
        $customer->setEmail('active_cust1@test.net');
        $manager->persist($customer);

        $customer = new Customer();
        $customer->setIdentification('678678678');
        $customer->setActive(1);
        $customer->setName('Active customer 678');
        $customer->setLegalName('Active customer 678 SSLL');
        $customer->setEmail('active_cust2@test.net');
        $manager->persist($customer);


        $customer = new Customer();
        $customer->setIdentification('12313213233');
        $customer->setActive(0);
        $customer->setName('Disabled customer 123');
        $customer->setLegalName('Disabled customer 123 SSLL');
        $customer->setEmail('disable_cust1@test.net');
        $manager->persist($customer);

        $manager->flush();
    }
}
