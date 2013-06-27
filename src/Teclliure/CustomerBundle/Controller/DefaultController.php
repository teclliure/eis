<?php

namespace Teclliure\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Teclliure\CustomerBundle\Form\Type\CustomerSearchType;
use Teclliure\CustomerBundle\Form\Type\CustomerExtendedSearchType;

class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        $searchData = array();
        $basicSearchForm = $this->createForm(new CustomerSearchType(), array());
        $basicSearchForm->handleRequest($request);
        $extendedSearchForm = $this->createForm(new CustomerExtendedSearchType(), array());
        $extendedSearchForm->handleRequest($request);
        if ($basicSearchForm->isValid()) {
            $searchData = $basicSearchForm->getData();
        }
        else if ($extendedSearchForm->isValid()) {
            $searchData = $extendedSearchForm->getData();
        }
        $customerService = $this->get('customer_service');
        $customers = $customerService->getCustomers(10,  $this->get('request')->query->get('page', 1), $searchData);
        if ($request->isXmlHttpRequest()) {
            return $this->render('TeclliureCustomerBundle:Default:customerList.html.twig', array(
                'customers'              => $customers
            ));
        }
        else {
            return $this->render('TeclliureCustomerBundle:Default:index.html.twig', array(
                'customers'             => $customers,
                'basicSearchForm'       => $basicSearchForm->createView(),
                'extendedSearchForm'    => $extendedSearchForm->createView()
            ));
        }
    }
}
