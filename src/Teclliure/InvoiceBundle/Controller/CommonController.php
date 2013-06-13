<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Teclliure\InvoiceBundle\Service\InvoiceService;
use Teclliure\InvoiceBundle\Entity\Common;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Teclliure\InvoiceBundle\Entity\CommonLine;

class CommonController extends Controller
{
    public function searchCustomerAction(Request $request)
    {
        $field = str_replace('invoice_customer_', '', $request->get('field'));
        $customerService = $this->get('customer_service');
        $customers = $customerService->searchCustomers(array($field=>$request->get('term')), 10);

        $returnArray = array();
        foreach ($customers as $customer) {
            $customerArray = array();
            $customerArray['label'] = $customer->getIdentification().' - '.$customer->getName();
            $customerArray['id'] = $customer->getId();
            $customerArray['identification'] = $customer->getIdentification();
            $customerArray['name'] = $customer->getName();
            $customerArray['address'] = $customer->getAddress();
            $customerArray['zip_code'] = $customer->getZipCode();
            $customerArray['city'] = $customer->getCity();
            $customerArray['state'] = $customer->getState();
            $customerArray['country'] = $customer->getCountry();
            $returnArray[] = $customerArray;
        }

        $callback = $request->get('callback');
        $response = new JsonResponse($returnArray, 200, array());
        $response->setCallback($callback);
        return $response;
    }

    public function updatePricesAction (Request $request) {
        $invoiceData = $request->get('invoice');
        $invoiceService = $this->get('invoice_service');
        if ($request->get('id')) {
            $invoice = $invoiceService->getInvoice($request->get('id'));
        }
        else {
            $invoice = $invoiceService->createInvoice();
        }
        $form = $this->createForm($this->get('teclliure.form.type.invoice'), $invoice);
        $form->bind($request);

        $taxRepository = $this->getDoctrine()->getRepository('TeclliureInvoiceBundle:Tax');
        $lines = array();
        foreach ($invoiceData['common_lines'] as $key=>$line) {
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
        // print_r ($invoice->getCommonLines());

        $renderedJs = $this->renderView('TeclliureInvoiceBundle:Common:updatePrice.js.twig', array('invoice'=>$invoice, 'lines'=>$lines));
        $response = new Response( $renderedJs );
        $response->headers->set( 'Content-Type', 'text/javascript' );

        return $response;
    }
}

