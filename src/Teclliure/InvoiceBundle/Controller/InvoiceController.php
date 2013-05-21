<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Teclliure\InvoiceBundle\Service\InvoiceService;
use Teclliure\InvoiceBundle\Entity\Invoice;
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

    public function addInvoiceAction(Request $request) {
        $form = $this->createForm(new InvoiceType(), new Invoice());

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->bind($request);
            if ($form->isValid()) {
                $invoice = $form->getData();

                $em = $this->getDoctrine()->getManager();
                $em->persist($invoice);
                $em->flush();
                // ... maybe do some form processing, like saving the Task and Tag objects
                return $this->redirect($this->generateUrl('homepage'));
            }
        }

        return $this->render('TeclliureInvoiceBundle:Invoice:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
