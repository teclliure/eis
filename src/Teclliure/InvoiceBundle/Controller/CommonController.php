<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Teclliure\InvoiceBundle\Service\InvoiceService;
use Teclliure\InvoiceBundle\Entity\Common;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}

