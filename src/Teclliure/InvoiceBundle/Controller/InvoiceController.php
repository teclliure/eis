<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Teclliure\InvoiceBundle\Service\InvoiceService;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\InvoiceBundle\Form\Type\InvoiceType;
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
        if ($request->get('id')) {
            $invoiceService = $this->get('invoice_service');
            $invoice = $invoiceService->getInvoice($request->get('id'));
        }
        else {
            $invoice = new Common();
        }
        $form = $this->createForm(new InvoiceType(), $invoice);

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->bind($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                /*foreach ($invoice->getCommonLines() as $line) {
                    print (count($line->getTaxes()));
                }
                exit();*/
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
