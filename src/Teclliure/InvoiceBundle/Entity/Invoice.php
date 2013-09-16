<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Teclliure\InvoiceBundle\Entity\Common;

/**
 * @ORM\Table(name="invoice", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="number_unique", columns={"number"})}
 * )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Invoice {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

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
    private $status = 0;

    /**
     * @var string $number
     *
     * @ORM\Column(type="string", length=25, unique=true, nullable=true )
     *
     */
    private $number;

    /**
     * @var date $issue_date
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="date")
     *
     */
    private $issue_date;

    /**
     * @var date $due_date
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="date")
     *
     */
    private $due_date;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min = 2, max = 10000)
     *
     */
    private $footnote;

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

    /**
     * @var float $base_amount
     *
     * @ORM\Column(type="float")
     *
     *
     */
    private $base_amount;

    /**
     * @var float $discount_amount
     *
     * @ORM\Column(type="float")
     *
     *
     */
    private $discount_amount;

    /**
     * @var float $net_amount
     *
     * @ORM\Column(type="float")
     *
     */
    private $net_amount;

    /**
     * @var float $tax_amount
     *
     * @ORM\Column(type="float")
     *
     *
     */
    private $tax_amount;

    /**
     * @var float $gross_amount
     *
     * @ORM\Column(type="float")
     *
     */
    private $gross_amount;

    /**
     * @var float $due_amount
     *
     * @ORM\Column(type="float")
     *
     */
    private $due_amount;

    /**
     * @ORM\OneToMany(targetEntity="Teclliure\InvoiceBundle\Entity\Payment", mappedBy="invoice", cascade={"persist", "remove"})
     * @var Invoice
     */
    protected $payments;

    /**
     * @ORM\Column(type="string", length=150, unique=true, nullable=true )
     *
     * @var String
     */
    protected $contact_name;

    /**
     *
     * @ORM\Column(type="string", length=150, unique=true, nullable=true )
     *
     * @var String
     */
    protected $contact_email;

    /**
     * Set status
     *
     * @param integer $status
     * @return Invoice
     */
    public function setStatus($status)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return integer 
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set number
     *
     * @param string $number
     * @return Invoice
     */
    public function setNumber($number)
    {
        $this->number = $number;
    
        return $this;
    }

    /**
     * Get number
     *
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set issue_date
     *
     * @param \DateTime $issueDate
     * @return Invoice
     */
    public function setIssueDate($issueDate)
    {
        $this->issue_date = $issueDate;
    
        return $this;
    }

    /**
     * Get issue_date
     *
     * @return \DateTime 
     */
    public function getIssueDate()
    {
        return $this->issue_date;
    }

    /**
     * Set due_date
     *
     * @param \DateTime $dueDate
     * @return Invoice
     */
    public function setDueDate($dueDate)
    {
        $this->due_date = $dueDate;
    
        return $this;
    }

    /**
     * Get due_date
     *
     * @return \DateTime 
     */
    public function getDueDate()
    {
        return $this->due_date;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Invoice
     */
    public function setCreated($created)
    {
        $this->created = $created;
    
        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime 
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Invoice
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    
        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime 
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set serie
     *
     * @param \Teclliure\InvoiceBundle\Entity\Serie $serie
     * @return Invoice
     */
    public function setSerie(\Teclliure\InvoiceBundle\Entity\Serie $serie = null)
    {
        $this->serie = $serie;
    
        return $this;
    }

    /**
     * Get serie
     *
     * @return \Teclliure\InvoiceBundle\Entity\Serie 
     */
    public function getSerie()
    {
        return $this->serie;
    }

    /**
     * Set base_amount
     *
     * @param float $baseAmount
     * @return Invoice
     */
    public function setBaseAmount($baseAmount)
    {
        $this->base_amount = $baseAmount;
    
        return $this;
    }

    /**
     * Get base_amount
     *
     * @return float 
     */
    public function getBaseAmount()
    {
        return $this->base_amount;
    }

    /**
     * Set discount_amount
     *
     * @param float $discountAmount
     * @return Invoice
     */
    public function setDiscountAmount($discountAmount)
    {
        $this->discount_amount = $discountAmount;
    
        return $this;
    }

    /**
     * Get discount_amount
     *
     * @return float 
     */
    public function getDiscountAmount()
    {
        return $this->discount_amount;
    }

    /**
     * Set net_amount
     *
     * @param float $netAmount
     * @return Invoice
     */
    public function setNetAmount($netAmount)
    {
        $this->net_amount = $netAmount;
    
        return $this;
    }

    /**
     * Get net_amount
     *
     * @return float 
     */
    public function getNetAmount()
    {
        return $this->net_amount;
    }

    /**
     * Set tax_amount
     *
     * @param float $taxAmount
     * @return Invoice
     */
    public function setTaxAmount($taxAmount)
    {
        $this->tax_amount = $taxAmount;
    
        return $this;
    }

    /**
     * Get tax_amount
     *
     * @return float 
     */
    public function getTaxAmount()
    {
        return $this->tax_amount;
    }

    /**
     * Set gross_amount
     *
     * @param float $grossAmount
     * @return Invoice
     */
    public function setGrossAmount($grossAmount)
    {
        $this->gross_amount = $grossAmount;
    
        return $this;
    }

    /**
     * Get gross_amount
     *
     * @return float 
     */
    public function getGrossAmount()
    {
        return $this->gross_amount;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * Get status name
     *
     *
     * @return string
     *
     */
    public function getStatusName() {
        if ($this->getStatus() == 0) {
            return 'Draft';
        }
        elseif ($this->getStatus() == 1) {
            return 'Closed';
        }
        elseif ($this->getStatus() == 2) {
            return 'Overdue';
        }
        elseif ($this->getStatus() == 3) {
            return 'Paid';
        }
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->payments = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Add payments
     *
     * @param \Teclliure\InvoiceBundle\Entity\Payment $payments
     * @return Invoice
     */
    public function addPayment(\Teclliure\InvoiceBundle\Entity\Payment $payments)
    {
        $this->payments[] = $payments;
    
        return $this;
    }

    /**
     * Remove payments
     *
     * @param \Teclliure\InvoiceBundle\Entity\Payment $payments
     */
    public function removePayment(\Teclliure\InvoiceBundle\Entity\Payment $payments)
    {
        $this->payments->removeElement($payments);
    }

    /**
     * Get payments
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPayments()
    {
        return $this->payments;
    }

    public function getPaidAmount() {
        $paid = 0;
        foreach ($this->getPayments() as $payment) {
            $paid += $payment->getAmount();
        }
        return $paid;
    }

    /**
     * Set due_amount
     *
     * @param float $dueAmount
     * @return Invoice
     */
    public function setDueAmount($dueAmount)
    {
        $this->due_amount = $dueAmount;
    
        return $this;
    }

    /**
     * Get due_amount
     *
     * @return float 
     */
    public function getDueAmount()
    {
        return $this->due_amount;
    }

    /**
     * Set footnote
     *
     * @param string $footnote
     * @return Invoice
     */
    public function setFootnote($footnote)
    {
        $this->footnote = $footnote;
    
        return $this;
    }

    /**
     * Get footnote
     *
     * @return string 
     */
    public function getFootnote()
    {
        return $this->footnote;
    }

    /**
     * Set contact_name
     *
     * @param string $contactName
     * @return Invoice
     */
    public function setContactName($contactName)
    {
        $this->contact_name = $contactName;
    
        return $this;
    }

    /**
     * Get contact_name
     *
     * @return string 
     */
    public function getContactName()
    {
        return $this->contact_name;
    }

    /**
     * Set contact_email
     *
     * @param string $contactEmail
     * @return Invoice
     */
    public function setContactEmail($contactEmail)
    {
        $this->contact_email = $contactEmail;
    
        return $this;
    }

    /**
     * Get contact_email
     *
     * @return string 
     */
    public function getContactEmail()
    {
        return $this->contact_email;
    }
}