<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="order")
 * @ORM\Entity
 */
class Order {
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Common", inversedBy="order", cascade={"persist"})
     * @ORM\JoinColumn(name="common_id", referencedColumnName="id")
     */
    private $common;

    /**
     * @var integer $number
     *
     * @ORM\Column(type="integer")
     *
     */
    private $number;

    /**
     *
     * Possible status are
     *  - DRAFT         - 0
     *  - INVOICED      - 1
     *
     * @var integer $number
     *
     * @ORM\Column(type="smallint")
     *
     */
    private $status;

    /**
     * @var datetime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     *
     */
    private $created;

    /**
     * @var datetime $updated
     *
     * @ORM\Column(type="datetime")
     * @Gedmo\Timestampable(on="update")
     *
     */
    private $updated;
}