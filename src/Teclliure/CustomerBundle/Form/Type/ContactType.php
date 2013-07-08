<?php

namespace Teclliure\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('name');
        $builder->add('email', 'email');
        $builder->add('phone');
        $builder->add('send_quote', null, array('required' => false));
        $builder->add('send_delivery_note', null, array('required' => false));
        $builder->add('send_invoice', null, array('required' => false));
        $builder->add('send_payment', null, array('required' => false));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\CustomerBundle\Entity\Contact'
        ));
    }

    public function getName()
    {
        return 'contact';
    }
}