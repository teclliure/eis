<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Teclliure\InvoiceBundle\Entity\CommonLine;
use Doctrine\Common\Util\Inflector;

class CommonController extends Controller
{
    public function searchCustomerAction(Request $request)
    {
        $baseObject = $request->get('base');
        $field = str_replace($baseObject.'_customer_', '', $request->get('field'));
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
    }
}

