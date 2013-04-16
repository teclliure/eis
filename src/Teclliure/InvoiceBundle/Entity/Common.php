<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="common")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Common {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Quote", mappedBy="common", cascade={"persist", "remove"})
     */
    private $quote;

    /**
     * @ORM\OneToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Order", mappedBy="common", cascade={"persist", "remove"})
     */
    private $order;

    /**
     * @ORM\OneToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Invoice", mappedBy="common", cascade={"persist", "remove"})
     */
    private $invoice;

}