<?php
/*
 * This file is part of Teclliure developed package build on 5/24/13.
 *
 * (c) Marc Montañés Abarca <marc@teclliure.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teclliure\invoiceBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Teclliure\InvoiceBundle\Entity\Tax;

class LoadTaxData implements FixtureInterface
{
    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $tax = new Tax();
        $tax->setName('IVA');
        $tax->setValue(21);
        $tax->setIsDefault(1);
        $tax->setActive(1);
        $manager->persist($tax);

        $tax = new Tax();
        $tax->setName('IVA Disabled');
        $tax->setValue(21);
        $tax->setIsDefault(1);
        $tax->setActive(0);
        $manager->persist($tax);

        $tax = new Tax();
        $tax->setName('IRPF');
        $tax->setValue(21);
        $tax->setIsDefault(0);
        $tax->setActive(1);
        $manager->persist($tax);

        $manager->flush();
    }
}