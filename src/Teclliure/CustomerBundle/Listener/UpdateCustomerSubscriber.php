<?php

namespace Teclliure\CustomerBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Teclliure\InvoiceBundle\Event\InvoiceEvent;
use Teclliure\InvoiceBundle\Event\QuoteEvent;
use Teclliure\InvoiceBundle\Event\DeliveryNoteEvent;
use Teclliure\InvoiceBundle\CommonEvents;

class UpdateCustomerSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public static function getSubscribedEvents ()
    {
        return array(
            CommonEvents::INVOICE_SAVED => array('updateInvoiceCustomer', -5),
            CommonEvents::QUOTE_SAVED => array('updateQuoteCustomer', -5),
            CommonEvents::DELIVERY_NOTE_SAVED => array('updateDeliveryNoteCustomer', -5)
        );
    }

    public function updateInvoiceCustomer (InvoiceEvent $event)
    {
        $invoice = $event->getInvoice();
        $this->updateCustomerFromCommon($invoice->getCommon());
    }

    public function updateQuoteCustomer (QuoteEvent $event)
    {
        $invoice = $event->getQuote();
        $this->updateCustomerFromCommon($invoice->getCommon());
    }

    public function updateDeliveryNoteCustomer (DeliveryNoteEvent $event)
    {
        $invoice = $event->getDeliveryNote();
        $this->updateCustomerFromCommon($invoice->getCommon());
    }

    /**
     *
     * Create a new customer if the identification doesn't exists or update current data if needed
     *
     * @param Common $common
     *
     * @api 0.1
     */
    protected function updateCustomerFromCommon(Common $common) {
        $customer = $common->getCustomer();
        if (!$customer) {
            $customer = $this->em->getRepository('TeclliureCustomerBundle:Customer')->findOneBy(array('identification'=>$common->getCustomerIdentification()));
            if (!$customer) {
                $customer = new Customer();
                $customer->setIdentification($common->getCustomerIdentification());
                $customer->setLegalName($common->getCustomerName());
                $customer->setAddress($common->getCustomerAddress());
                $customer->setZipCode($common->getCustomerZipCode());
                $customer->setCity($common->getCustomerCity());
                $customer->setState($common->getCustomerState());
                $customer->setCountry($common->getCustomerCountry());
            }
            $common->setCustomer($customer);
        }
        else {
            if (!$customer->getLegalName()) {
                $customer->setLegalName($common->getCustomerName());
            }
            if (!$customer->getAddress()) {
                $customer->setAddress($common->getCustomerAddress());
            }
            if (!$customer->getZipCode()) {
                $customer->setZipCode($common->getCustomerZipCode());
            }
            if (!$customer->getCity()) {
                $customer->setCity($common->getCustomerCity());
            }
            if (!$customer->getState()) {
                $customer->setState($common->getCustomerState());
            }
            if (!$customer->getCountry()) {
                $customer->setCountry($common->getCustomerCountry());
            }
        }
    }
}