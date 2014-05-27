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

    public function orderQuoteAction(Request $request) {
        $t = $this->get('translator');
        $quoteService = $this->get('quote_service');
        $quote = $quoteService->getQuote($request->get('id'));
        if (!$quote) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Quote does not exists!'));
            return $this->redirect($this->generateUrl('quote_list'));
        }
        elseif ($quote->getStatus() != 1) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Quote with status different to pending could not be ordered.'));
            return $this->redirect($this->generateUrl('quote_list'));
        }
        $this->get('session')->getFlashBag()->add('info', $t->trans('Quote to order!'));
        return $this->redirect($this->generateUrl('delivery_note_edit', array('id'=>$quote->getId(), 'new'=>true)));
    }

    public function addEditDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $originalLines = array();
        $deliveryNoteService = $this->get('delivery_note_service');
        $relatedQuote = null;
        if ($request->get('id')) {
            $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'), $request->get('new'));
            if (!$deliveryNote) {
                $this->get('session')->getFlashBag()->add('warning', $t->trans('Order does not exists!'));
                return $this->redirect($this->generateUrl('delivery_note_list'));
            }
            elseif ($deliveryNote->getStatus() != 0 ) {
                $this->get('session')->getFlashBag()->add('warning', $t->trans('Order with status different to draft could not be edited.'));
                return $this->redirect($this->generateUrl('delivery_note_list'));
            }

            // Create an array of the current CommonLines objects in the database
            foreach ($deliveryNote->getCommon()->getCommonLines() as $commonLine) {
                $originalLines[] = $commonLine;
            }
        }
        else {
            $deliveryNote = $deliveryNoteService->createDeliveryNote();
        }
        if ($request->get('relatedQuote')) {
            $relatedQuote = $request->get('relatedQuote');
        }
        $form = $this->createForm($this->get('teclliure.form.type.delivery_note'), $deliveryNote);

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $deliveryNoteService->saveDeliveryNote($deliveryNote, $originalLines, $relatedQuote);

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
            'urlParams' => array('id'=>$deliveryNote->getId(), 'relatedQuote'=>$relatedQuote),
            'deliveryNote'    => $deliveryNote
        ));
    }

    public function viewInvoiceAction(Request $request) {
        $t = $this->get('translator');
        $invoiceService = $this->get('invoice_service');
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
        $type = $request->get('type');
        $id = $request->get('id');
        $invoices = $invoiceService->getInvoicesView(10,  $this->get('request')->query->get('page', 1), $id, $type, $searchData);

        if (!$invoices || count($invoices)<1) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('No invoices found'));
            return $this->redirect($this->generateUrl('invoice_list'));
        }

        if (count($invoices) == 1) {
            return $this->render('TeclliureInvoiceBundle:Invoice:invoicePrint.html.twig', array(
                'invoice' => $invoices[0],
                'config' => $this->get('craue_config')->all(),
                'print'  => false,
            ));
        }
        else {
            $related = $invoiceService->getRelatedObject($type, $id);
            if ($request->isXmlHttpRequest()) {
                return $this->render('TeclliureInvoiceBundle:Invoice:invoiceList.html.twig', array(
                    'invoices'      => $invoices,
                    'searchData'    => serialize($searchData),
                    'related'       => $related,
                    'relatedClass'  => $type
                ));
            }
            else {
                return $this->render('TeclliureInvoiceBundle:Invoice:index.html.twig', array(
                    'invoices'              => $invoices,
                    'searchData'            => serialize($searchData),
                    'basicSearchForm'       => $basicSearchForm->createView(),
                    'extendedSearchForm'    => $extendedSearchForm->createView(),
                    'related'               => $related,
                    'relatedClass'          => $type
                ));
            }
        }
    }

    public function viewDeliveryNoteAction(Request $request) {
        $t = $this->get('translator');
        $deliveryNoteService = $this->get('delivery_note_service');
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
        $type = $request->get('type');
        $id = $request->get('id');
        $deliveryNotes = $deliveryNoteService->getDeliveryNotesView(10,  $this->get('request')->query->get('page', 1), $id, $type, $searchData);
        if (!$deliveryNotes || count($deliveryNotes) < 1) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Order does not exists!'));
            return $this->redirect($this->generateUrl('delivery_note_list'));
        }

        if (count($deliveryNotes) == 1) {
            return $this->render('TeclliureInvoiceBundle:DeliveryNote:deliveryNotePrint.html.twig', array(
                'deliveryNote' => $deliveryNotes[0],
                'config' => $this->get('craue_config')->all(),
                'print'  => false
            ));
        }
        else {
            $related = $deliveryNoteService->getRelatedObject($type, $id);
            if ($request->isXmlHttpRequest()) {
                return $this->render('TeclliureInvoiceBundle:DeliveryNote:deliveryNoteList.html.twig', array(
                    'deliveryNotes' => $deliveryNotes,
                    'searchData'    => serialize($searchData),
                    'related'       => $related,
                    'relatedClass'  => $type
                ));
            }
            else {
                return $this->render('TeclliureInvoiceBundle:DeliveryNote:index.html.twig', array(
                    'deliveryNotes'         => $deliveryNotes,
                    'searchData'            => serialize($searchData),
                    'basicSearchForm'       => $basicSearchForm->createView(),
                    'extendedSearchForm'    => $extendedSearchForm->createView(),
                    'related'               => $related,
                    'relatedClass'          => $type
                ));
            }
        }
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

        $headerHtml = $this->renderView('TeclliureInvoiceBundle:Common:headerPdf.html.twig', array(
          'config' => $this->get('craue_config')->all(),
        ));

        $footerHtml = $this->renderView('TeclliureInvoiceBundle:Common:footerPdf.html.twig', array(
          'config' => $this->get('craue_config')->all(),
        ));

        $pdfRenderer = $this->get('knp_snappy.pdf');
        return new Response(
            $pdfRenderer->getOutputFromHtml($html, array('header-html'=>$headerHtml, 'footer-html'=> $footerHtml, 'margin-left'=> '2mm', 'margin-top'=> '4mm', 'margin-bottom'=>'5mm')),
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
        $invoiceService = $this->get('invoice_service');
        $deliveryNote = $deliveryNoteService->getDeliveryNote($request->get('id'));
        if (!$deliveryNote) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Order does not exists!'));
            return $this->redirect($this->generateUrl('delivery_note_list'));
        }
        $this->get('session')->getFlashBag()->add('info', $t->trans('Order to invoice!'));
        try {
            $invoice = $deliveryNoteService->createInvoiceFromDeliveryNote($deliveryNote);
        }
        catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans($e->getMessage()));
            return $this->redirect($this->generateUrl('quote_list'));
        }

        $invoiceService->putDefaults($invoice);
        $form = $this->createForm($this->get('teclliure.form.type.invoice'), $invoice);

        return $this->render('TeclliureInvoiceBundle:Invoice:invoiceForm.html.twig', array(
            'form'      => $form->createView(),
            'config'    => $this->get('craue_config')->all(),
            'urlParams' => array('id'=>$invoice->getId(), 'relatedDeliveryNote'=>$deliveryNote->getId()),
            'invoice'   => $invoice
        ));
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
