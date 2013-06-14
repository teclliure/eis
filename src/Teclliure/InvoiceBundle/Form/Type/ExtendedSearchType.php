<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Teclliure\InvoiceBundle\Form\Type\SearchType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;

class ExtendedSearchType extends SearchType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder
            ->add('i_start_issue_date','date',array(
                'widget'        => 'single_text',
                'format'        => 'dd/MM/yyyy',
                'label'         => 'Issue start date',
                'constraints'   => array(
                    new Date(),
                    new Callback(array(
                        'methods' => array('Teclliure\InvoiceBundle\Form\StaticValidation'=>'checkIssueStartEnd'),
                    )))
                )
            )
            ->add('i_end_issue_date','date',array(
                'widget'        => 'single_text',
                'format'        => 'dd/MM/yyyy',
                'label'         => 'Issue end date',
                'constraints'   => array(
                    new Date()
                    )
                )
            )
            ->add('i_serie','entity',array(
                'class'     => 'Teclliure\InvoiceBundle\Entity\Serie',
                'label'     => 'Serie'
                )
            )
            ->add('i_status','choice',array(
                'choices'   => array(
                    '0'     => 'Draft',
                    '1'     => 'Closed',
                    '2'     => 'Overdue',
                    '3'     => 'Paid',
                ),
                'label'     => 'Status',
                'multiple'  => true,
                'expanded'  => true
            ))
            ->add('c_customer_name','text', array(
                'label'     => 'Customer',
            ))
            ;
    }
}