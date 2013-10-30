<?php

namespace Teclliure\InvoiceBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Teclliure\InvoiceBundle\Entity\DeliveryNote;

class DeliveryNoteEvent extends Event
{
    private $deliveryNote;

    public function __construct(DeliveryNote $deliveryNote)
    {
        $this->deliveryNote = $deliveryNote;
    }

    public function getDeliveryNote()
    {
        return $this->deliveryNote;
    }
}