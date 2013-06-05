<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Teclliure\InvoiceBundle\Form\Type\CommonLineType;
use Teclliure\InvoiceBundle\Entity\CommonLine;
use Doctrine\ORM\EntityManager;

class CommonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->add('id', 'hidden');
        $builder->add('description');
        $builder->add('customer', 'hidden');
        $builder->add('customer_name');
        $builder->add('customer_identification');
        $builder->add('customer_zip_code');
        $builder->add('customer_address');
        $builder->add('customer_city');
        $builder->add('customer_state');
        $builder->add('customer_country');
        $builder->add('common_lines', 'collection', array(
                'type'           => new CommonLineType(),
                'allow_add'      => true,
                'allow_delete'   => true,
                'by_reference'   => false,
                'required'       => true,
                'label'          => false,
                'prototype_data' => $this->getPrototypeCommonLine()
        ));

        /*
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
        });*/
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\Common'
        ));
    }

    public function getName()
    {
        return 'common';
    }

    protected function getPrototypeCommonLine() {
        // Build object for prototype
        $commonLinePrototype = new CommonLine();
        $query = $this->em->createQuery('SELECT t FROM TeclliureInvoiceBundle:Tax t where t.active = 1 AND t.is_default = 1');
        $taxes = $query->getResult();
        foreach ($taxes as $tax) {
            $commonLinePrototype->addTax($tax);
        }
        return $commonLinePrototype;
    }
}