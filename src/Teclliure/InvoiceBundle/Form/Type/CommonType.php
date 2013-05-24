<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Teclliure\InvoiceBundle\Form\Type\CommonLineType;

class CommonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->add('id', 'hidden');
        $builder->add('description');
        $builder->add('customer_name');
        $builder->add('customer_identification');
        $builder->add('customer_zip_code');
        $builder->add('customer_address');
        $builder->add('customer_city');
        $builder->add('customer_state');
        $builder->add('customer_country');
        $builder->add('common_lines', 'collection', array(
            'type' => new CommonLineType(),
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
            )
        );
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
}