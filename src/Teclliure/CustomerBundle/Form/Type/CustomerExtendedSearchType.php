<?php

namespace Teclliure\CustomerBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\EventDispatcher\Event;

class CustomerExtendedSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$options['constraints'] = array(
            new Callback(array(array($this,'checkIssueStartEnd')))
        );*/
        $builder
            ->add('c_name','text', array(
                'label'     => 'Name',
                'required' => false
            ))
            ->add('c_identification','text', array(
                'label'     => 'Identification',
                'required' => false
            ))
            ->add('c_email','text', array(
                'label'     => 'Email',
                'required' => false
            ))
            ->add('c_state','text', array(
                'label'     => 'State',
                'required' => false
            ))
            ->add('c_country','country', array(
                'label'     => 'Country',
                'required' => false
            ))
            ->add('c_active','choice',array(
                'choices'   => array(
                    ''     => 'Yes and no',
                    '0'     => 'Yes',
                    '1'     => 'No'
                ),
                'label'     => 'Active',
                'multiple'  => false,
                'expanded'  => false,
                'required' => false,
            ))
            ;
    }

    public function getName()
    {
        return 'extended_search';
    }
}