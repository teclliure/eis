<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;

class PaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*
        * This allows show all used taxes, including the ones that
        * has been deactivated after invoice creation.
        */
        $builder->addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) {
            $form = $event->getForm();
            $data = $event->getData();

            if ($data && $data->getId()) {
                $id = $data->getId();
            }
            else {
                $id = null;
            }
        });

        $builder->add('notes');
        $builder->add('amount');
        $builder->add('payment_date', 'date', array(
            'widget' => 'single_text',
            'format' => 'dd/MM/yyyy'
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\Payment'
        ));
    }

    public function getName()
    {
        return 'payment';
    }
}