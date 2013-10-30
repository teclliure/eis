<?php

namespace Teclliure\InvoiceBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Teclliure\InvoiceBundle\Entity\Invoice;

class InvoiceEvent extends Event
{
    private $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    public function getInvoice()
    {
        return $this->invoice;
    }
}