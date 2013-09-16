<?php

namespace Teclliure\InvoiceBundle;

final class CommonEvents
{
    /**
    * This event occurs when a invoice is closed
    *
    * The event listener receives an
    * Teclliure\InvoiceBundle\Event\CommonEvent instance.
    *
    * @var string
    */
    const INVOICE_CLOSED = 'invoice.closed';

    /**
     * This event occurs when a quote is closed
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\CommonEvent instance.
     *
     * @var string
     */
    const QUOTE_CLOSED = 'quote.closed';

    /**
     * This event occurs when a delivery note is closed
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\CommonEvent instance.
     *
     * @var string
     */
    const DELIVERY_NOTE_CLOSED = 'delivery_note.closed';
}