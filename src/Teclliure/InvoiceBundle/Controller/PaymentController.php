<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\InvoiceBundle\Form\Type\PaymentType;

class PaymentController extends Controller
{
    public function paymentsAction(Common $common)
    {
        $paymentService = $this->get('payment_service');
        $payments = $paymentService->searchPayments(array('invoice'=>$common->getInvoice()->getId()));

        $form = $this->createForm(new PaymentType(), $paymentService->createPayment());

        return $this->render('TeclliureInvoiceBundle:Payment:payments.html.twig', array(
            'invoice'                => $common->getInvoice(),
            'payments'              => $payments,
            'paymentForm'           => $form->createView()
        ));
    }

    public function addAction (Request $request) {
        $t = $this->get('translator');
        $invoiceService = $this->get('invoice_service');
        $paymentService = $this->get('payment_service');

        $invoice = $invoiceService->getInvoiceById($request->get('invoice_id'));
        if (!$invoice) {
            $this->get('session')->getFlashBag()->add('error', $t->trans('Invoice does not exist'));
        }
        else {
            $payment = $paymentService->createPayment();
            $form = $this->createForm(new PaymentType(), $payment);

            // process the form on POST
            if ($request->isMethod('post')) {
                $form->handleRequest($request);

                if ($form->isValid()) {
                    try {
                        $payment->setInvoice($invoice);
                        $paymentService->savePayment($payment);

                        $this->get('session')->getFlashBag()->add('info', $t->trans('Payment added.'));
                        $form = $this->createForm(new PaymentType(), $paymentService->createPayment());
                    }
                    catch (\Exception $e) {
                        $this->get('session')->getFlashBag()->add('error', $t->trans('Payment error'.': '.$e->getMessage()));
                    }
                }
            }
            $payments = $paymentService->searchPayments(array('invoice'=>$invoice->getId()));
        }

        return $this->render('TeclliureInvoiceBundle:Payment:modalBody.html.twig', array(
            'invoice'               => $invoice,
            'payments'              => $payments,
            'paymentForm'           => $form->createView()
        ));
    }

    public function deleteAction (Request $request, $payment_id) {
        $t = $this->get('translator');
        $invoiceService = $this->get('invoice_service');
        $paymentService = $this->get('payment_service');

        $payment = $paymentService->getPayment($request->get('payment_id'));
        if (!$payment) {
            $this->get('session')->getFlashBag()->add('error', $t->trans('Payment does not exist'));
        }
        else {
            $invoice = $payment->getInvoice();
            $paymentService->deletePayment($payment);
            $form = $this->createForm(new PaymentType(), $paymentService->createPayment());
            $payments = $paymentService->searchPayments(array('invoice'=>$invoice->getId()));
            $this->get('session')->getFlashBag()->add('info', $t->trans('Payment deleted.'));
        }

        return $this->render('TeclliureInvoiceBundle:Payment:modalBody.html.twig', array(
            'invoice'               => $invoice,
            'payments'              => $payments,
            'paymentForm'           => $form->createView()
        ));
    }
/*
    public function searchCustomerNameAction(Request $request)
    {
        $customerService = $this->get('customer_service');
        $customers = $customerService->searchCustomers(array('name'=>$request->get('term'), 'identification'=>$request->get('term')), 10, 0, 'OR');

        $returnArray = array();
        foreach ($customers as $customer) {
            $customerArray = array();
            $customerArray['label'] = $customer->getName();
            $returnArray[] = $customerArray;
        }

        $callback = $request->get('callback');
        $response = new JsonResponse($returnArray, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    public function updatePricesAction (Request $request) {
        $baseObject = $request->get('baseObject');
        $data = $request->get($baseObject);
        $commonService = $this->get($baseObject.'_service');
        $inflector = new Inflector();
        $className = ucfirst($inflector->camelize($baseObject));
        if ($request->get('id')) {
            $methodName = 'get'.$className;
            $common = $commonService->$methodName($request->get('id'));
        }
        else {
            $methodName = 'create'.$className;
            $common = $commonService->$methodName();
        }
        $form = $this->createForm($this->get('teclliure.form.type.'.$baseObject), $common);
        $form->handleRequest($request);

        $taxRepository = $this->getDoctrine()->getRepository('TeclliureInvoiceBundle:Tax');
        $lines = array();
        foreach ($data['common_lines'] as $key=>$line) {
            $commonLine = new CommonLine();
            $commonLine->setQuantity($line['quantity']);
            $commonLine->setDiscount($line['discount']);
            $commonLine->setUnitaryCost($line['unitary_cost']);

            foreach ($line['taxes'] as $tax) {
                $tax = $taxRepository->find($tax);
                if ($tax) {
                    $commonLine->addTax($tax);
                }
            }
            $lines['invoice_common_common_lines_'.$key] = $commonLine;
        }

        $renderedJs = $this->renderView('TeclliureInvoiceBundle:Common:updatePrice.js.twig', array('common'=>$common, 'lines'=>$lines));
        $response = new Response( $renderedJs );
        $response->headers->set( 'Content-Type', 'text/javascript' );

        return $response;
    }*/
}

