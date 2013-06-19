<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Teclliure\InvoiceBundle\Entity\Invoice;
use Teclliure\InvoiceBundle\Service\InvoiceService;
use Teclliure\InvoiceBundle\Entity\Common;
use Symfony\Component\HttpFoundation\Request;
use Teclliure\InvoiceBundle\Form\Type\SearchType;
use Teclliure\InvoiceBundle\Form\Type\ExtendedSearchType;

class InvoiceController extends Controller
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
        $invoiceService = $this->get('invoice_service');
        $invoices = $invoiceService->getInvoices(2,  $this->get('request')->query->get('page', 1), $searchData);
        if ($request->isXmlHttpRequest()) {
            return $this->render('TeclliureInvoiceBundle:Invoice:invoiceList.html.twig', array(
                'invoices'              => $invoices
            ));
        }
        else {
            return $this->render('TeclliureInvoiceBundle:Invoice:index.html.twig', array(
                'invoices'              => $invoices,
                'basicSearchForm'       => $basicSearchForm->createView(),
                'extendedSearchForm'    => $extendedSearchForm->createView()
            ));
        }
    }

    public function addEditInvoiceAction(Request $request) {
        $originalLines = array();
        $invoiceService = $this->get('invoice_service');
        if ($request->get('id')) {
            $invoice = $invoiceService->getInvoice($request->get('id'));

            // Create an array of the current CommonLines objects in the database
            foreach ($invoice->getCommonLines() as $commonLine) {
                $originalLines[] = $commonLine;
            }
        }
        else {
            $invoice = $invoiceService->createInvoice();
        }
        $form = $this->createForm($this->get('teclliure.form.type.invoice'), $invoice);

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $invoiceService->saveInvoice($invoice, $originalLines);

                $action = $request->get('action');
                if ($action == 'save_and_close') {
                    $invoiceService->closeInvoice($invoice);
                    $this->get('session')->getFlashBag()->add('success', $t->trans('Invoice saved and closed!'));
                }
                elseif ($action == 'save') {
                    $this->get('session')->getFlashBag()->add('success', $t->trans('Invoice saved!'));
                }
                else {
                    $this->get('session')->getFlashBag()->add('warning', $t->trans('Nothing done!'));
                }

                return $this->redirect($this->generateUrl('invoice_list'));
            }
        }

        return $this->render('TeclliureInvoiceBundle:Invoice:invoiceForm.html.twig', array(
            'form' => $form->createView(),
            'common' => $invoice
        ));
    }

    public function openInvoiceAction(Request $request) {
        $t = $this->get('translator');
        $invoiceService = $this->get('invoice_service');
        $invoice = $invoiceService->getInvoice($request->get('id'));
        $invoiceService->openInvoice($invoice);

        $this->get('session')->getFlashBag()->add('info', $t->trans('Invoice re-opened!'));

        return $this->redirect($this->generateUrl('invoice_edit', array ('id'=>$invoice->getId())));
    }

    protected function notFoundRedirect ($id) {
        $t = $this->get('translator');
        $this->get('session')->getFlashBag()->add('error', $t->trans('Invoice with id ('.$id.') not found!'));
        return $this->redirect($this->generateUrl('invoice_list'));
    }

    protected function notEditableRedirect ($id) {
        $t = $this->get('translator');
        $this->get('session')->getFlashBag()->add('error', $t->trans('Invoice with id ('.$id.') cannot be edited!'));
        return $this->redirect($this->generateUrl('invoice_list'));
    }
}
