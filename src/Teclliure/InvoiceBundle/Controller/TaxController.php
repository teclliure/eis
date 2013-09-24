<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Teclliure\InvoiceBundle\Entity\Tax;
use Teclliure\InvoiceBundle\Form\Type\TaxType;

/**
 * Tax controller.
 *
 * @Route("/tax")
 */
class TaxController extends Controller
{
    /**
     * Lists all Tax entities.
     *
     * @Route("/", name="tax")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TeclliureInvoiceBundle:Tax')->findAll();

        foreach ($entities as $entity) {
            $result = $this->getDoctrine()->getManager()
                ->createQuery('SELECT cl FROM TeclliureInvoiceBundle:CommonLine cl JOIN cl.taxes t WHERE t.id = :taxId')
                ->setParameter('taxId', $entity->getId())
                ->setMaxResults(1)
                ->getOneOrNullResult();


            if (!$result) {
                $entity->setIsEmpty(true);
            }
        }

        return array(
            'entities' => $entities,
        );
    }

    /**
     * Creates a new Tax entity.
     *
     * @Route("/", name="tax_create")
     * @Method("POST")
     * @Template("TeclliureInvoiceBundle:Tax:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $t = $this->get('translator');
        $entity  = new Tax();
        $form = $this->createForm(new TaxType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', $t->trans('Tax saved'));

            return $this->redirect($this->generateUrl('tax'));
        }

        $this->get('session')->getFlashBag()->add('error', $t->trans('Error saving Tax'));

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Tax entity.
     *
     * @Route("/new", name="tax_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Tax();
        $form   = $this->createForm(new TaxType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Tax entity.
     *
     * @Route("/{id}", name="tax_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TeclliureInvoiceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Tax entity.
     *
     * @Route("/{id}/edit", name="tax_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TeclliureInvoiceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $editForm = $this->createForm(new TaxType(), $entity);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Edits an existing Tax entity.
     *
     * @Route("/{id}", name="tax_update")
     * @Method("POST")
     * @Template("TeclliureInvoiceBundle:Tax:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TeclliureInvoiceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $editForm = $this->createForm(new TaxType(), $entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('tax_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Disable a Tax entity.
     *
     * @Route("/disable/{id}", name="tax_disable")
     * @Method("GET")
     */
    public function disableAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TeclliureInvoiceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }
        $entity->setActive(0);
        $em->persist($entity);
        // $em->remove($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('tax'));
    }

    /**
     * Enable a Tax entity.
     *
     * @Route("/enable/{id}", name="tax_enable")
     * @Method("GET")
     */
    public function enableAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TeclliureInvoiceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }
        $entity->setActive(1);
        $em->persist($entity);
        $em->flush();

        return $this->redirect($this->generateUrl('tax'));
    }

    /**
     * Delete a Tax entity.
     *
     * @Route("/delete/{id}", name="tax_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TeclliureInvoiceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }
        // FIXME: Memory problems when there are too many lines
        if (count ($entity->getLines()) > 0) {
            $this->get('session')->getFlashBag()->add('error', $t->trans('Error deleting Tax: this Tax is used in lines.'));
        }
        else {
            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', $t->trans('Tax removed'));
        }

        return $this->redirect($this->generateUrl('tax'));
    }
}
