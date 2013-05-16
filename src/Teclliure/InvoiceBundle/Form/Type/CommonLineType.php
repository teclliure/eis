<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CommonLineType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('id', 'hidden');
        $builder->add('description');
        $builder->add('quantity');
        $builder->add('unitary_cost');
        $builder->add('discount');
        $builder->add('taxes', 'collection', array(
            'type' => new TaxType(),
            'allow_add'    => true,
            'allow_delete' => true,
            'by_reference' => false,
            )
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\CommonLine'
        ));
    }

    public function getName()
    {
        return 'common_line';
    }
}