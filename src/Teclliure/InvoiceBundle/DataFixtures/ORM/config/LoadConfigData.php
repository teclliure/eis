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
        $setting->setValue('Test company name');
        $setting->setSortOrder(0);
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_identification');
        $setting->setType('text');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(1);
        $setting->setValue('1231231234T');
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_web');
        $setting->setType('text');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(3);
        $setting->setValue('http://www.test.net');
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_mail');
        $setting->setType('text');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array());
        $setting->setSortOrder(2);
        $setting->setValue('sales@test.net');
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('company_address');
        $setting->setType('textarea');
        $setting->setSection('company_info');
        $setting->setTypeOptions(array('attr' => array('class' => 'input-large', 'rows' => 3)));
        $setting->setSortOrder(4);
        $setting->setValue("1123 Wilson Way.\n World");
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

        $setting = new Setting();
        $setting->setName('default_footnote_quote');
        $setting->setType('textarea');
        $setting->setValue('Default footnote quote. Change on settings.');
        $setting->setSection('web');
        $setting->setTypeOptions(array('attr' => array('class' => 'input-xxlarge', 'rows' => 5)));
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('default_footnote_order');
        $setting->setType('textarea');
        $setting->setValue('Default footnote order. Change on settings.');
        $setting->setSection('web');
        $setting->setTypeOptions(array('attr' => array('class' => 'input-xxlarge', 'rows' => 5)));
        $manager->persist($setting);

        $setting = new Setting();
        $setting->setName('default_footnote_invoice');
        $setting->setType('textarea');
        $setting->setValue('Default footnote invoice. Change on settings.');
        $setting->setSection('web');
        $setting->setTypeOptions(array('attr' => array('class' => 'input-xxlarge', 'rows' => 5)));
        $manager->persist($setting);

        $manager->flush();
    }
}