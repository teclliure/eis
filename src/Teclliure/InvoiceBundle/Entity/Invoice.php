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
     * @var integer $number
     *
     * @ORM\Column(type="integer", unique=true, nullable=true )
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

    public function calculateBaseAmount(Common $common)
    {
        $amount = 0;
        $lines = $common->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getBaseAmount();
        }
        return $amount;
    }

    public function calculateDiscountAmount(Common $common)
    {
        $amount = 0;
        $lines = $common->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getDiscountAmount();
        }
        return $amount;
    }

    public function calculateNetAmount(Common $common)
    {
        $amount = 0;
        $lines = $common->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getNetAmount();
        }
        return $amount;
    }

    public function calculateTaxAmount(Common $common)
    {
        $amount = 0;
        $lines = $common->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getTaxAmount();
        }
        return $amount;
    }

    public function calculateGrossAmount(Common $common)
    {
        $amount = 0;
        $lines = $common->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getTotalAmount();
        }
        return $amount;
    }

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
     * @param integer $number
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
     * @return integer 
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
}