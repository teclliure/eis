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

    public function addInvoiceAction(Request $request) {
        $invoice = new Common();
        $form = $this->createForm(new InvoiceType(), $invoice);

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->bind($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($invoice);
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', $t->trans('Invoice saved!'));

                return $this->redirect($this->generateUrl('homepage'));
            }
            else {
                $this->get('session')->getFlashBag()->add('error', $t->trans('Invoice NOT saved. Error in form: '.$form->getErrorsAsString()));
            }
        }

        return $this->render('TeclliureInvoiceBundle:Invoice:new.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
