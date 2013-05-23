<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class TaxType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('value')
            ->add('active', 'checkbox', array(
                'label'     => 'Is active ?',
                'required'  => false,
            ))
            ->add('is_default', 'checkbox', array(
                'label'     => 'Is default ?',
                'required'  => false,
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\Tax'
        ));
    }

    public function getName()
    {
        return 'teclliure_invoicebundle_taxtype';
    }
}
