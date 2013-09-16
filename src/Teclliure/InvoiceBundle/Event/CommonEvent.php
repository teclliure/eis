<?php

namespace Teclliure\InvoiceBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Teclliure\InvoiceBundle\Entity\Common;

class CommonEvent extends Event
{
    private $common;

    public function __construct(Common $common)
    {
        $this->common = $common;
    }

    public function getCommon()
    {
        return $this->common;
    }
}