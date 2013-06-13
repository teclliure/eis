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
use Craue\ConfigBundle\Entity\Setting;

class LoadConfigData implements FixtureInterface
{
    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $setting = new Setting();
        $setting->setName('company_name');
        $setting->setType('text');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(0);
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_identification');
        $setting->setType('text');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(1);
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_web');
        $setting->setType('text');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(3);
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_mail');
        $setting->setType('text');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(2);
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_address');
        $setting->setType('textarea');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(4);
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('default_serie');
        $setting->setType('entity');
        $setting->setSection('web');
        $setting->setTypeOptions(array('class' => 'TeclliureInvoiceBundle:Serie'));
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('default_country');
        $setting->setType('country');
        $setting->setValue('ES');
        $setting->setSection('web');
        $setting->setTypeOptions(array());
        $manager->persist($setting);

        $manager->flush();
    }
}