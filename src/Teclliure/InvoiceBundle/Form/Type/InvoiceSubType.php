<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Teclliure\InvoiceBundle\Form\Type\CommonType;

class InvoiceSubType extends CommonType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('issue_date', 'date', array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy'
        ));
        $builder->add('due_date', 'date', array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy'
        ));
        $builder->add('footnote');
        $builder->add('serie');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\Invoice'
        ));
    }

    public function getName()
    {
        return 'invoiceSub';
    }
}
