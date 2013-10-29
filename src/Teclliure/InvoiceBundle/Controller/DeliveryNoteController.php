<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Teclliure\InvoiceBundle\Form\Type\SearchType;
use Teclliure\InvoiceBundle\Form\Type\DeliveryNoteExtendedSearchType as ExtendedSearchType;

class DeliveryNoteController extends Controller
{
    public function indexAction(Request $request)
    {
        $searchData = array();
        $basicSearchForm = $this->createForm(new SearchType(), array());
        $basicSearchForm->handleRequest($request);
        $extendedSearchForm = $this->createForm(new ExtendedSearchType(), array());
        $extendedSearchForm->handleRequest($request);
        if ($basicSearchForm->isValid()) {
            $searchData = $basicSearchForm->getData();
        }
        else if ($extendedSearchForm->isValid()) {
            $searchData = $extendedSearchForm->getData();
        }
        $deliveryNoteService = $this->get('delivery_note_service');
        $deliveryNotes = $deliveryNoteService->getDeliveryNotes(10,  $this->get('request')->query->get('page', 1), $searchData);
        if ($request->isXmlHttpRequest()) {
            return $this->render('TeclliureInvoiceBundle:DeliveryNote:deliveryNoteList.html.twig', array(
                'deliveryNotes'              => $deliveryNotes
            ));
        }
        else {
            return $this->render('TeclliureInvoiceBundle:DeliveryNote:index.html.twig', array(
                'deliveryNotes'              => $deliveryNotes,
                'basicSearchForm'       => $basicSearchForm->createView(),
                'extendedSearchForm'    => $extendedSearchForm->createView()
            ));
        }
    }

    public function addEditDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $originalLines = array();
        $deliveryNoteService = $this->get('delivery_note_service');
        if ($request->get('id')) {
            $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'), $request->get('new'));
            if (!$deliveryNote) {
                $this->get('session')->getFlashBag()->add('warning', $t->trans('Order does not exists!'));
                return $this->redirect($this->generateUrl('delivery_note_list'));
            }
            elseif (!$request->get('new') && $deliveryNote->getStatus() != 0 ) {
                $this->get('session')->getFlashBag()->add('warning', $t->trans('Order with status different to draft could not be edited.'));
                return $this->redirect($this->generateUrl('delivery_note_list'));
            }
            if ($request->get('new')) {
                $deliveryNoteService->putDefaults($deliveryNote);
            }
            // Create an array of the current CommonLines objects in the database
            foreach ($deliveryNote->getCommon()->getCommonLines() as $commonLine) {
                $originalLines[] = $commonLine;
            }
        }
        else {
            $deliveryNote = $deliveryNoteService->createDeliveryNote();
        }
        $form = $this->createForm($this->get('teclliure.form.type.delivery_note'), $deliveryNote);

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $deliveryNoteService->saveDeliveryNote($deliveryNote, $originalLines);

                $action = $request->get('action');
                if ($action == 'save_and_close') {
                    $deliveryNoteService->closeDeliveryNote($deliveryNote);
                    $this->get('session')->getFlashBag()->add('success', $t->trans('Order saved and closed!'));
                }
                elseif ($action == 'save') {
                    $this->get('session')->getFlashBag()->add('success', $t->trans('Order saved!'));
                }
                else {
                    $this->get('session')->getFlashBag()->add('warning', $t->trans('Nothing done!'));
                }

                return $this->redirect($this->generateUrl('delivery_note_list'));
            }
        }

        return $this->render('TeclliureInvoiceBundle:DeliveryNote:deliveryNoteForm.html.twig', array(
            'form'      => $form->createView(),
            'config'    => $this->get('craue_config')->all(),
            'new'       => $request->get('new'),
            'deliveryNote'    => $deliveryNote
        ));
    }

    public function viewDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $deliveryNoteService = $this->get('delivery_note_service');
        $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'));

        if (!$deliveryNote) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Order does not exists!'));
            return $this->redirect($this->generateUrl('delivery_note_list'));
        }

        return $this->render('TeclliureInvoiceBundle:DeliveryNote:deliveryNotePrint.html.twig', array(
            'deliveryNote' => $deliveryNote,
            'config' => $this->get('craue_config')->all(),
            'print'  => false
        ));
    }

    public function printDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $deliveryNoteService = $this->get('delivery_note_service');
        $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'));

        if (!$deliveryNote) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Order does not exists!'));
            return $this->redirect($this->generateUrl('delivery_note_list'));
        }

        $html = $this->renderView('TeclliureInvoiceBundle:DeliveryNote:deliveryNotePrint.html.twig', array(
            'deliveryNote' => $deliveryNote,
            'config' => $this->get('craue_config')->all(),
            'print'  => true
        ));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="deliveryNote'.$deliveryNote->getCreated()->format('d-m-Y').$deliveryNote->getNumber().'.pdf"'
            )
        );
    }

    public function openDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $deliveryNoteService = $this->get('delivery_note_service');
        $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'));

        if ($deliveryNote->getStatus() > 1) {
            $this->get('session')->getFlashBag()->add('error', $t->trans('Order cannot be re-opened! It already generated an invoice.'));
            return $this->redirect($this->generateUrl('delivery_note_list'));
        }
        else {
            $deliveryNoteService->openDeliveryNote($deliveryNote);
            $this->get('session')->getFlashBag()->add('info', $t->trans('Order re-opened!'));
        }

        return $this->redirect($this->generateUrl('delivery_note_edit', array ('id'=>$deliveryNote->getId())));
    }

    public function closeDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $deliveryNoteService = $this->get('delivery_note_service');
        $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'));
        $deliveryNoteService->closeDeliveryNote($deliveryNote);

        $this->get('session')->getFlashBag()->add('info', $t->trans('Order closed!'));

        return $this->redirect($this->generateUrl('delivery_note_list'));
    }

    public function invoiceDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $deliveryNoteService = $this->get('delivery_note_service');
        $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'));
        $this->get('session')->getFlashBag()->add('info', $t->trans('Order to invoice!'));
        return $this->redirect($this->generateUrl('invoice_edit', array('id'=>$deliveryNote->getId(), 'new'=>true)));
    }

    protected function notFoundRedirect ($id) {
        $t = $this->get('translator');
        $this->get('session')->getFlashBag()->add('error', $t->trans('Order with id ('.$id.') not found!'));
        return $this->redirect($this->generateUrl('delivery_note_list'));
    }

    protected function notEditableRedirect ($id) {
        $t = $this->get('translator');
        $this->get('session')->getFlashBag()->add('error', $t->trans('Order with id ('.$id.') cannot be edited!'));
        return $this->redirect($this->generateUrl('delivery_note_list'));
    }
}
