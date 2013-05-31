<?php

namespace Teclliure\InvoiceBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormEvents;

class CommonLineType extends AbstractType
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

            $form->add('taxes','entity', array(
                'class'     => 'TeclliureInvoiceBundle:Tax',
                'expanded'  => true,
                'multiple'  => true,
                'query_builder' => function (EntityRepository $er) use ($id) {
                    $queryBoulder = $er->createQueryBuilder('t')
                        ->leftJoin('t.lines', 'l')
                        ->where('t.active = 1')
                        ->add('orderBy', 't.name ASC');

                    if ($id) {
                        $queryBoulder
                        ->orWhere('l.id = :line')
                        ->setParameter('line', $id);
                    }

                    return $queryBoulder;
                }
            ));
        });

        //$builder->add('id', 'hidden');
        $builder->add('description');
        $builder->add('quantity');
        $builder->add('unitary_cost');
        $builder->add('discount');
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Teclliure\InvoiceBundle\Entity\CommonLine'
        ));
    }

    public function getName()
    {
        return 'common_line';
    }
}