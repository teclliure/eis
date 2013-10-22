<?php

namespace Teclliure\CustomerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Teclliure\CustomerBundle\Form\Type\CustomerSearchType;
use Teclliure\CustomerBundle\Form\Type\CustomerExtendedSearchType;
use Teclliure\CustomerBundle\Form\Type\CustomerType;

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

    public function addEditAction(Request $request) {
        $originalContacts = array();
        $customerService = $this->get('customer_service');
        if ($request->get('id')) {
            $customer = $customerService->getCustomer($request->get('id'));
            // Create an array of the current CommonLines objects in the database
            foreach ($customer->getContacts() as $contact) {
                $originalContacts[] = $contact;
            }
        }
        else {
            $customer = $customerService->createCustomer();
        }
        $form = $this->createForm(new CustomerType(), $customer);
        // process the form on POST
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $customerService->saveCustomer($customer, $originalContacts);
                $this->get('session')->getFlashBag()->add('success', $t->trans('Customer saved!'));

                return $this->redirect($this->generateUrl('customer_list'));
            }
        }

        return $this->render('TeclliureCustomerBundle:Default:customerForm.html.twig', array(
            'form' => $form->createView(),
            'customer' => $customer
        ));
    }

    public function disableCustomerAction(Request $request) {
        $t = $this->get('translator');
        $customerService = $this->get('customer_service');
        $customer = $customerService->getCustomer($request->get('id'));
        $customerService->disableCustomer($customer);

        $this->get('session')->getFlashBag()->add('info', $t->trans('Customer disabled!'));

        return $this->redirect($this->generateUrl('customer_list'));
    }

    public function enableCustomerAction(Request $request) {
        $t = $this->get('translator');
        $customerService = $this->get('customer_service');
        $customer = $customerService->getCustomer($request->get('id'));
        $customerService->enableCustomer($customer);

        $this->get('session')->getFlashBag()->add('info', $t->trans('Customer enabled!'));

        return $this->redirect($this->generateUrl('customer_list'));
    }

    public function deleteAction(Request $request) {
        $t = $this->get('translator');
        $customerService = $this->get('customer_service');
        $customer = $customerService->getCustomer($request->get('id'));
        $customerService->deleteCustomer($customer);

        $this->get('session')->getFlashBag()->add('info', $t->trans('Customer deleted!'));

        return $this->redirect($this->generateUrl('customer_list'));
    }

}
