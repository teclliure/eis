<?php

namespace Teclliure\InvoiceBundle\Listener;

use Doctrine\ORM\EntityManager;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Teclliure\InvoiceBundle\Event\InvoiceEvent;
use Teclliure\InvoiceBundle\Event\QuoteEvent;
use Teclliure\InvoiceBundle\Event\DeliveryNoteEvent;
use Teclliure\InvoiceBundle\CommonEvents;
use Teclliure\InvoiceBundle\Service\CommonService;

class StatusChangeSubscriber implements EventSubscriberInterface
{
    private $commonService;
    private $em;

    public function __construct(CommonService $commonService, EntityManager $em)
    {
        $this->commonService = $commonService;
        $this->em = $em;
    }

    public static function getSubscribedEvents ()
    {
        return array(
            CommonEvents::INVOICE_SAVED => array('onInvoiceSaved', -5),
            // CommonEvents::QUOTE_SAVED => array('onQuoteSaved', -5),
            CommonEvents::DELIVERY_NOTE_SAVED => array('onDeliveryNoteSaved', -5)
        );
    }

    public function onInvoiceSaved (InvoiceEvent $event)
    {
        $invoice = $event->getInvoice();
        $relatedQuote = $invoice->getRelatedQuote();
        $relatedDeliveryNote = $invoice->getRelatedDeliveryNote();

        if ($relatedDeliveryNote) {
            $existingInvoices = $this->em->getRepository('TeclliureInvoiceBundle:Invoice')->findBy(array('related_delivery_note'=>$relatedDeliveryNote->getId()));
            $commons = array();
            foreach ($existingInvoices as $tmpInvoice) {
                $commons[] = $tmpInvoice;
            }
            if ($this->commonService->isCommonCovered($relatedDeliveryNote->getCommon(), $commons)) {
                $relatedDeliveryNote->setStatus(2);
            }
            else {
                $relatedDeliveryNote->setStatus(3);
            }
            unset ($commons);
            $this->em->persist($relatedDeliveryNote);
        }
        if ($relatedQuote) {
            $existingInvoices = $this->em->getRepository('TeclliureInvoiceBundle:Invoice')->findBy(array('related_quote'=>$relatedQuote->getId()));
            $commons = array();
            foreach ($existingInvoices as $tmpInvoice) {
                $commons[] = $tmpInvoice;
            }
            if ($this->commonService->isCommonCovered($relatedQuote->getCommon(), $commons)) {
                $relatedQuote->setStatus(4);
            }
            else {
                $relatedQuote->setStatus(5);
            }
            unset ($commons);
            $this->em->persist($relatedQuote);
        }

        $this->em->flush();
    }

/*    public function onQuoteSaved (QuoteEvent $event)
    {
        $quote = $event->getQuote();
    }*/

    public function onDeliveryNoteSaved (DeliveryNoteEvent $event)
    {
        $deliveryNote = $event->getDeliveryNote();
        $relatedQuote = $deliveryNote->getRelatedQuote();
        if ($relatedQuote && $relatedQuote->getStatus() < 2) {
            $relatedQuote->setStatus(3);
            $this->em->persist($relatedQuote);
        }
        $this->em->flush();
    }
}