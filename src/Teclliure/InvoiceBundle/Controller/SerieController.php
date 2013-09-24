<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Teclliure\InvoiceBundle\Entity\Serie;
use Teclliure\InvoiceBundle\Form\Type\SerieType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Serie controller.
 *
 * @Route("/serie")
 */
class SerieController extends Controller
{
    /**
     * Lists all Serie entities.
     *
     * @Route("/", name="serie")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('TeclliureInvoiceBundle:Serie')->findAll();

        foreach ($entities as $entity) {
            $result = $this->getDoctrine()->getManager()
                ->createQuery('SELECT i FROM TeclliureInvoiceBundle:Invoice i WHERE i.serie = :serieId')
                ->setParameter('serieId', $entity->getId())
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
     * Creates a new Serie entity.
     *
     * @Route("/", name="serie_create")
     * @Method("POST")
     * @Template("TeclliureInvoiceBundle:Serie:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $t = $this->get('translator');
        $entity  = new Serie();
        $form = $this->createForm(new SerieType(), $entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', $t->trans('Serie saved'));

            return $this->redirect($this->generateUrl('serie'));
        }

        $this->get('session')->getFlashBag()->add('error', $t->trans('Error saving Serie'));

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Displays a form to create a new Serie entity.
     *
     * @Route("/new", name="serie_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Serie();
        $form   = $this->createForm(new SerieType(), $entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Serie entity.
     *
     * @Route("/{id}", name="serie_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TeclliureInvoiceBundle:Serie')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Serie entity.
     *
     * @Route("/{id}/edit", name="serie_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TeclliureInvoiceBundle:Serie')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }

        $editForm = $this->createForm(new SerieType(), $entity);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView()
        );
    }

    /**
     * Edits an existing Serie entity.
     *
     * @Route("/{id}", name="serie_update")
     * @Method("POST")
     * @Template("TeclliureInvoiceBundle:Serie:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('TeclliureInvoiceBundle:Serie')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }

        $editForm = $this->createForm(new SerieType(), $entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->persist($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', $t->trans('Serie saved'));

            return $this->redirect($this->generateUrl('serie_edit', array('id' => $id)));
        }
        $this->get('session')->getFlashBag()->add('error', $t->trans('Error saving Serie'));


        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        );
    }

    /**
     * Disable a Serie entity.
     *
     * @Route("/disable/{id}", name="serie_disable")
     * @Method("GET")
     */
    public function disableAction(Request $request, $id)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TeclliureInvoiceBundle:Serie')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }
        $entity->setActive(0);
        $em->persist($entity);
        // $em->remove($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', $t->trans('Serie disabled'));

        return $this->redirect($this->generateUrl('serie'));
    }

    /**
     * Enable a Serie entity.
     *
     * @Route("/enable/{id}", name="serie_enable")
     * @Method("GET")
     */
    public function enableAction(Request $request, $id)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TeclliureInvoiceBundle:Serie')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }
        $entity->setActive(1);
        $em->persist($entity);
        $em->flush();
        $this->get('session')->getFlashBag()->add('info', $t->trans('Serie enabled'));

        return $this->redirect($this->generateUrl('serie'));
    }

    /**
     * Delete a Serie entity.
     *
     * @Route("/delete/{id}", name="serie_delete")
     * @Method("GET")
     */
    public function deleteAction(Request $request, $id)
    {
        $t = $this->get('translator');
        $em = $this->getDoctrine()->getManager();
        $entity = $em->getRepository('TeclliureInvoiceBundle:Serie')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Serie entity.');
        }
        // FIXME: Memory problems when there are too many invoices
        if (count ($entity->getInvoices()) > 0) {
            $this->get('session')->getFlashBag()->add('error', $t->trans('Error deleting Serie: this Serie is used in invoices.'));
        }
        else {
            $em->remove($entity);
            $em->flush();

            $this->get('session')->getFlashBag()->add('info', $t->trans('Serie removed'));
        }

        return $this->redirect($this->generateUrl('serie'));
    }
}
