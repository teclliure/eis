<?php

namespace Teclliure\InvoiceBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Teclliure\InvoiceBundle\Entity\Quote;

class QuoteEvent extends Event
{
    private $quote;

    public function __construct(Quote $quote)
    {
        $this->quote = $quote;
    }

    public function getQuote()
    {
        return $this->quote;
    }
}