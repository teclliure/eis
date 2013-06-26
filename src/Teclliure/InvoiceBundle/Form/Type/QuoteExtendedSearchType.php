<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Teclliure\InvoiceBundle\Form\Type\SearchType;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\ExecutionContext;
use Symfony\Component\EventDispatcher\Event;

class QuoteExtendedSearchType extends SearchType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*$options['constraints'] = array(
            new Callback(array(array($this,'checkIssueStartEnd')))
        );*/
        parent::buildForm($builder, $options);
        $builder
            ->add('start_issue_date','date',array(
                'widget'        => 'single_text',
                'format'        => 'dd/MM/yyyy',
                'label'         => 'Issue start date',
                'required' => false,
                'constraints'   => array(
                    new Date()
                    )
                )
            )
            ->add('end_issue_date','date',array(
                'widget'        => 'single_text',
                'format'        => 'dd/MM/yyyy',
                'label'         => 'Issue end date',
                'required' => false,
                'constraints'   => array(
                    new Date()
                    )
                )
            )
            ->add('q_status','choice',array(
                'choices'   => array(
                    '0'     => 'Draft',
                    '1'     => 'Pending',
                    '2'     => 'Rejected',
                    '3'     => 'Aprpoved',
                ),
                'label'     => 'Status',
                'multiple'  => true,
                'expanded'  => true,
                'required' => false,
            ))
            ->add('c_customer_name','text', array(
                'label'     => 'Customer',
                'required' => false
            ))
            ;
    }

    public function getName()
    {
        return 'extended_search';
    }

//    public static function checkIssueStartEnd($data, ExecutionContextInterface $context)
//    {
//        print_r ($data);
//        if (isset($data['i_start_issue_date']) && isset($data['i_end_issue_date'])) {
//            $context->addViolationAt('i_start_issue_date', 'Start date must be bigger than end date!', array(), null);
//        }
//        return $data;
//    }
}