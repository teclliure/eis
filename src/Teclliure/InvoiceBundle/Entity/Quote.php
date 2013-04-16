<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="quote")
 * @ORM\Entity
 */
class Quote {
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Common", inversedBy="quote", cascade={"persist"})
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
     *  - REJECTED      - 1
     *  - PENDING       - 2
     *  - APPROVED      - 3
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