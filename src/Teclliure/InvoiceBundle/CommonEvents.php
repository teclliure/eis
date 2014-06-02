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

    /**
     * This event occurs when a invoice is going to be saved
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\InvoiceEvent instance.
     *
     * @var string
     */
    const INVOICE_PRE_SAVED = 'invoice.presaved';

    /**
     * This event occurs when a quote is going to be saved
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\QuoteEvent instance.
     *
     * @var string
     */
    const QUOTE_PRE_SAVED = 'quote.presaved';

    /**
     * This event occurs when a delivery note is going to be saved
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\DeliveryEvent instance.
     *
     * @var string
     */
    const DELIVERY_NOTE_PRE_SAVED = 'delivery_note.presaved';

    /**
     * This event occurs when a invoice is saved
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\InvoiceEvent instance.
     *
     * @var string
     */
    const INVOICE_SAVED = 'invoice.saved';

    /**
     * This event occurs when a quote is saved
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\QuoteEvent instance.
     *
     * @var string
     */
    const QUOTE_SAVED = 'quote.saved';

    /**
     * This event occurs when a delivery note is saved
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\DeliveryEvent instance.
     *
     * @var string
     */
    const DELIVERY_NOTE_SAVED = 'delivery_note.saved';

    /**
     * This event occurs when a invoice is deleted
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\InvoiceEvent instance.
     *
     * @var string
     */
    const INVOICE_DELETED = 'invoice.deleted';

    /**
     * This event occurs when a quote is deleted
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\QuoteEvent instance.
     *
     * @var string
     */
    const QUOTE_DELETED = 'quote.deleted';

    /**
     * This event occurs when a delivery note is deleted
     *
     * The event listener receives an
     * Teclliure\InvoiceBundle\Event\DeliveryEvent instance.
     *
     * @var string
     */
    const DELIVERY_NOTE_DELETED = 'delivery_note.deleted';
}