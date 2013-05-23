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
        $entity  = new Tax();
        $form = $this->createForm(new TaxType(), $entity);
        $form->bind($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('tax'));
        }

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
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Edits an existing Tax entity.
     *
     * @Route("/{id}", name="tax_update")
     * @Method("PUT")
     * @Template("TeclliureInvoiceBundle:Tax:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TeclliureInvoiceBundle:Tax')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Tax entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createForm(new TaxType(), $entity);
        $editForm->bind($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('tax_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
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
     * Creates a form to delete a Tax entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder(array('id' => $id))
            ->add('id', 'hidden')
            ->getForm()
        ;
    }
}
