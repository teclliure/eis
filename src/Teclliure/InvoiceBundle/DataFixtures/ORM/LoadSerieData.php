<?php
/*
 * This file is part of Teclliure developed package build on 5/24/13.
 *
 * (c) Marc Montañés Abarca <marc@teclliure.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teclliure\InvoiceBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Teclliure\InvoiceBundle\Entity\Serie;

class LoadSerieData implements FixtureInterface
{
    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $serie = new Serie();
        $serie->setName('Internet');
        $serie->setActive(true);
        $serie->setShort('INT');
        $manager->persist($serie);

        $serie = new Serie();
        $serie->setName('Shop');
        $serie->setActive(true);
        $serie->setShort('SHO');
        $manager->persist($serie);

        /*$serie = new Serie();
        $serie->setName('Shop');
        $serie->setActive(true);
        $serie->setShort('SHO');
        $manager->persist($serie);*/

        $serie = new Serie();
        $serie->setName('Disabled');
        $serie->setActive(false);
        $serie->setShort('DIS');
        $manager->persist($serie);

        $manager->flush();
    }
}