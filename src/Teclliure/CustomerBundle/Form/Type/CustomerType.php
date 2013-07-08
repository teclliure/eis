<?php

namespace Teclliure\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Teclliure\CustomerBundle\Form\Type\ContactType;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // $builder->add('id', 'hidden');
        $builder->add('name');
        $builder->add('legal_name');
        $builder->add('identification');
        $builder->add('email', 'email',array('required' => false));
        $builder->add('phone', null, array('required' => false));
        $builder->add('web', 'url', array('required' => false));
        $builder->add('zip_code', null, array('required' => false));
        $builder->add('address', null, array('required' => false));
        $builder->add('city', null, array('required' => false));
        $builder->add('state', null, array('required' => false));
        $builder->add('country', null, array('required' => false));
        $builder->add('payment_period', null, array('required' => false));
        $builder->add('payment_day', null, array('required' => false));
        $builder->add('contacts', 'collection', array(
            'type'           => new ContactType(),
            'allow_add'      => true,
            'allow_delete'   => true,
            'by_reference'   => false,
            'required'       => true,
            'label'          => false
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
            'data_class' => 'Teclliure\CustomerBundle\Entity\Customer'
        ));
    }

    public function getName()
    {
        return 'customer';
    }
}