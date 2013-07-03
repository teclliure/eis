<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Teclliure\InvoiceBundle\Entity\Common;

/**
 * @ORM\Table(name="payment")
 * @ORM\Entity
 */
class Payment {
     /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var float $amount
     *
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     *
     */
    private $amount;

    /**
     * @var date $payment_date
     *
     * @ORM\Column(type="date")
     *
     * @Assert\NotBlank()
     * @Assert\Date()
     *
     */
    private $payment_date;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 2, max = 200)
     *
     */
    private $notes;


    /**
     * @ORM\ManyToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Invoice", inversedBy="payments")
     * @var Common
     */
    protected $invoice;

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
     * Set amount
     *
     * @param float $amount
     * @return Payment
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
    
        return $this;
    }

    /**
     * Get amount
     *
     * @return float 
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set payment_date
     *
     * @param \DateTime $paymentDate
     * @return Payment
     */
    public function setPaymentDate($paymentDate)
    {
        $this->payment_date = $paymentDate;
    
        return $this;
    }

    /**
     * Get payment_date
     *
     * @return \DateTime 
     */
    public function getPaymentDate()
    {
        return $this->payment_date;
    }

    /**
     * Set notes
     *
     * @param string $notes
     * @return Payment
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;
    
        return $this;
    }

    /**
     * Get notes
     *
     * @return string 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set invoice
     *
     * @param \Teclliure\InvoiceBundle\Entity\Invoice $invoice
     * @return Payment
     */
    public function setInvoice(\Teclliure\InvoiceBundle\Entity\Invoice $invoice = null)
    {
        $this->invoice = $invoice;
    
        return $this;
    }

    /**
     * Get invoice
     *
     * @return \Teclliure\InvoiceBundle\Entity\Invoice 
     */
    public function getInvoice()
    {
        return $this->invoice;
    }
}