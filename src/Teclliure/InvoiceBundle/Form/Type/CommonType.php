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
        $builder->add('description');
        $builder->add('customerName');
        $builder->add('customerIdentification');
        $builder->add('customerZipCode');
        $builder->add('customerAddress');
        $builder->add('customerCity');
        $builder->add('customerState');
        $builder->add('customerCountry');
        $builder->add('commonLines', 'collection', array(
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