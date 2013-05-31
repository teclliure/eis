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
                $em = $this->getDoctrine()->getManager();
                $em->persist($invoice);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $t->trans('Invoice saved!'));

                return $this->redirect($this->generateUrl('invoice_list'));
            }
        }

        return $this->render('TeclliureInvoiceBundle:Invoice:invoiceForm.html.twig', array(
            'form' => $form->createView(),
            'common' => $invoice
        ));
    }
}
