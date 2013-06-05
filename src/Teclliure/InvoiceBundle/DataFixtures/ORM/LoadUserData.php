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
use Teclliure\UserBundle\Entity\User;

class LoadUserData implements FixtureInterface
{
    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $user = new User();
        $user->setUsername('marc');
        $user->setEmail('marc@teclliure.net');
        $user->setPlainPassword('marc');
        $user->setEnabled(1);

        $manager->persist($user);

        $user = new User();
        $user->setUsername('marc_dis');
        $user->setEmail('marc_dis@teclliure.net');
        $user->setPlainPassword('marc');
        $user->setEnabled(0);

        $manager->persist($user);

        $manager->flush();
    }
}