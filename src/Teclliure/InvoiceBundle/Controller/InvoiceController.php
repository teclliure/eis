<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Teclliure\InvoiceBundle\Service\InvoiceService;
use Teclliure\InvoiceBundle\Entity\Common;
use Symfony\Component\HttpFoundation\Request;

class InvoiceController extends Controller
{
    public function indexAction()
    {
        $invoiceService = $this->get('invoice_service');
        $invoices = $invoiceService->getInvoices();

        return $this->render('TeclliureInvoiceBundle:Invoice:index.html.twig', array('invoices' => $invoices));
    }

    public function addEditInvoiceAction(Request $request) {
        $invoiceService = $this->get('invoice_service');
        if ($request->get('id')) {
            $invoice = $invoiceService->getInvoice($request->get('id'));
        }
        else {
            $invoice = $invoiceService->createInvoice();
        }
        $form = $this->createForm($this->get('teclliure.form.type.invoice'), $invoice);

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->bind($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $invoiceService->saveInvoice($invoice);

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
