<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Teclliure\InvoiceBundle\Form\Type\CommonType;
use Doctrine\ORM\EntityManager;

class DeliveryNoteType extends AbstractType
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('footnote');
        $builder->add('contact_name');
        $builder->add('contact_email');
        $builder->add('common', new CommonType($this->em), array(
            'label'          => false
        ));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\DeliveryNote'
        ));
    }

    public function getName()
    {
        return 'delivery_note';
    }
}
