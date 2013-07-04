<?php

namespace Teclliure\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->add('id', 'hidden');
        $builder->add('name');
        $builder->add('legal_name');
        $builder->add('identification');
        $builder->add('email', 'email');
        $builder->add('phone');
        $builder->add('web', 'url');
        $builder->add('zip_code');
        $builder->add('address');
        $builder->add('city');
        $builder->add('state');
        $builder->add('country');
        $builder->add('payment_period');
        $builder->add('payment_day');

        /*
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();
        });*/
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\CustomerBundle\Entity\Customer'
        ));
    }

    public function getName()
    {
        return 'customer';
    }
}