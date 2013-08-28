<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class SerieType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name')
            ->add('short')
            ->add('first_number')
            ->add('active', 'checkbox', array(
                'label'     => 'Is active ?',
                'required'  => false,
            ))

        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\Serie'
        ));
    }

    public function getName()
    {
        return 'teclliure_invoicebundle_serietype';
    }
}
