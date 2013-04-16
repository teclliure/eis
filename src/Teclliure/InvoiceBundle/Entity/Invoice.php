<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="invoice")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Invoice {
    /**
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Common", inversedBy="invoice", cascade={"persist"})
     * @ORM\JoinColumn(name="common_id", referencedColumnName="id")
     */
    private $common;

    /**
     *
     * Possible status are
     *  - DRAFT         - 0
     *  - CLOSED        - 1
     *  - OVERDUE       - 2
     *  - PAID          - 3
     *
     * @var integer $number
     *
     * @ORM\Column(type="smallint")
     *
     */
    private $status;

    /**
     * @var integer $number
     *
     * @ORM\Column(type="integer")
     *
     */
    private $number;

    /**
     * @var datetime $issue_date
     *
     * @ORM\Column(type="datetime")
     *
     */
    private $issue_date;

    /**
     * @var datetime $due_date
     *
     * @ORM\Column(type="datetime")
     *
     */
    private $due_date;

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

    /**
     *
     * @ORM\ManyToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Serie", inversedBy="invoices")
     *
     */
    private $serie;

}