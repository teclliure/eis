<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Teclliure\InvoiceBundle\Form\Type\SearchType;
use Teclliure\InvoiceBundle\Form\Type\ExtendedSearchType;

class QuoteController extends Controller
{
    public function indexAction(Request $request)
    {
        $searchData = array();
        $basicSearchForm = $this->createForm(new SearchType(), array());
        $basicSearchForm->handleRequest($request);
        $extendedSearchForm = $this->createForm(new ExtendedSearchType(), array());
        $extendedSearchForm->handleRequest($request);
        if ($basicSearchForm->isValid()) {
            $searchData = $basicSearchForm->getData();
        }
        else if ($extendedSearchForm->isValid()) {
            $searchData = $extendedSearchForm->getData();
        }
        $quoteService = $this->get('quote_service');
        $quotes = $quoteService->getQuotes(10,  $this->get('request')->query->get('page', 1), $searchData);
        if ($request->isXmlHttpRequest()) {
            return $this->render('TeclliureInvoiceBundle:Quote:quoteList.html.twig', array(
                'quotes'              => $quotes
            ));
        }
        else {
            return $this->render('TeclliureInvoiceBundle:Quote:index.html.twig', array(
                'quotes'              => $quotes,
                'basicSearchForm'       => $basicSearchForm->createView(),
                'extendedSearchForm'    => $extendedSearchForm->createView()
            ));
        }
    }

    public function addEditQuoteAction(Request $request) {
        $t = $this->get('translator');
        $originalLines = array();
        $quoteService = $this->get('quote_service');
        if ($request->get('id')) {
            $quote = $quoteService->getQuote($request->get('id'));
            if (!$quote) {
                $this->get('session')->getFlashBag()->add('warning', $t->trans('Quote does not exists!'));
                return $this->redirect($this->generateUrl('quote_list'));
            }
            elseif ($quote->getQuote()->getStatus() != 0) {
                $this->get('session')->getFlashBag()->add('warning', $t->trans('Quote with status different to draft could not be edited.'));
                return $this->redirect($this->generateUrl('quote_list'));
            }

            // Create an array of the current CommonLines objects in the database
            foreach ($quote->getCommonLines() as $commonLine) {
                $originalLines[] = $commonLine;
            }
        }
        else {
            $quote = $quoteService->createQuote();
        }
        $form = $this->createForm($this->get('teclliure.form.type.quote'), $quote);

        // process the form on POST
        if ($request->isMethod('post')) {
            $form->handleRequest($request);

            $t = $this->get('translator');
            if ($form->isValid()) {
                $quoteService->saveQuote($quote, $originalLines);

                $action = $request->get('action');
                if ($action == 'save_and_close') {
                    $quoteService->closeQuote($quote);
                    $this->get('session')->getFlashBag()->add('success', $t->trans('Quote saved and closed!'));
                }
                elseif ($action == 'save') {
                    $this->get('session')->getFlashBag()->add('success', $t->trans('Quote saved!'));
                }
                else {
                    $this->get('session')->getFlashBag()->add('warning', $t->trans('Nothing done!'));
                }

                return $this->redirect($this->generateUrl('quote_list'));
            }
        }

        return $this->render('TeclliureInvoiceBundle:Quote:quoteForm.html.twig', array(
            'form' => $form->createView(),
            'common' => $quote
        ));
    }

    public function viewQuoteAction(Request $request) {
        $t = $this->get('translator');
        $quoteService = $this->get('quote_service');
        $quote = $quoteService->getQuote($request->get('id'));

        if (!$quote) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Quote does not exists!'));
            return $this->redirect($this->generateUrl('quote_list'));
        }

        return $this->render('TeclliureInvoiceBundle:Quote:quotePrint.html.twig', array(
            'common' => $quote,
            'config' => $this->get('craue_config')->all(),
            'print'  => false
        ));
    }

    public function printQuoteAction(Request $request) {
        $t = $this->get('translator');
        $quoteService = $this->get('quote_service');
        $quote = $quoteService->getQuote($request->get('id'));

        if (!$quote) {
            $this->get('session')->getFlashBag()->add('warning', $t->trans('Quote does not exists!'));
            return $this->redirect($this->generateUrl('quote_list'));
        }

        $html = $this->renderView('TeclliureInvoiceBundle:Quote:quotePrint.html.twig', array(
            'common' => $quote,
            'config' => $this->get('craue_config')->all(),
            'print'  => true
        ));

        return new Response(
            $this->get('knp_snappy.pdf')->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'attachment; filename="quote'.$quote->getQuote()->getIssueDate()->format('d-m-Y').$quote->getQuote()->getNumber().'.pdf"'
            )
        );
    }

    public function openQuoteAction(Request $request) {
        $t = $this->get('translator');
        $quoteService = $this->get('quote_service');
        $quote = $quoteService->getQuote($request->get('id'));
        $quoteService->openQuote($quote);

        $this->get('session')->getFlashBag()->add('info', $t->trans('Quote re-opened!'));

        return $this->redirect($this->generateUrl('quote_edit', array ('id'=>$quote->getId())));
    }

    public function closeQuoteAction(Request $request) {
        $t = $this->get('translator');
        $quoteService = $this->get('quote_service');
        $quote = $quoteService->getQuote($request->get('id'));
        $quoteService->closeQuote($quote);

        $this->get('session')->getFlashBag()->add('info', $t->trans('Quote closed!'));

        return $this->redirect($this->generateUrl('quote_list'));
    }

    protected function notFoundRedirect ($id) {
        $t = $this->get('translator');
        $this->get('session')->getFlashBag()->add('error', $t->trans('Quote with id ('.$id.') not found!'));
        return $this->redirect($this->generateUrl('quote_list'));
    }

    protected function notEditableRedirect ($id) {
        $t = $this->get('translator');
        $this->get('session')->getFlashBag()->add('error', $t->trans('Quote with id ('.$id.') cannot be edited!'));
        return $this->redirect($this->generateUrl('quote_list'));
    }
}
