<?php

namespace Teclliure\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CustomerSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('search','search',array(
                'required' => false,
                'constraints' => array(
                    new Length(array(
                            'min'=>3
                    )),
                )
            ));
    }

    public function getName()
    {
        return 'search';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(

                'data_class'        => null,
            )
        );
    }
}