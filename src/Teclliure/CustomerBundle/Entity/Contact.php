<?php

namespace Teclliure\CustomerBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="customer_contact")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Contact {
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue
    */
    private $id;

    /**
     * @ORM\Column(type="string", length=200)
     *
     * @Assert\Length(min = 4, max = 200)
     * @Assert\NotBlank()
     *
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 5, max = 200)
     * @Assert\Email(
     *     message = "The email '{{ value }}' is not a valid email.",
     *     checkMX = true
     * )
     *
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 5, max = 200)
     *
     */
    private $phone;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $send_quote = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $send_delivery_note = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $send_invoice = false;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $send_payment = false;

    /**
     * @ORM\ManyToOne(targetEntity="Teclliure\CustomerBundle\Entity\Customer", inversedBy="contacts")
     * @var Customer
     */
    protected $customer;

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
     * Set name
     *
     * @param string $name
     * @return Contact
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Contact
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Contact
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set send_quote
     *
     * @param boolean $sendQuote
     * @return Contact
     */
    public function setSendQuote($sendQuote)
    {
        $this->send_quote = $sendQuote;
    
        return $this;
    }

    /**
     * Get send_quote
     *
     * @return boolean 
     */
    public function getSendQuote()
    {
        return $this->send_quote;
    }

    /**
     * Set send_delivery_note
     *
     * @param boolean $sendDeliveryNote
     * @return Contact
     */
    public function setSendDeliveryNote($sendDeliveryNote)
    {
        $this->send_delivery_note = $sendDeliveryNote;
    
        return $this;
    }

    /**
     * Get send_delivery_note
     *
     * @return boolean 
     */
    public function getSendDeliveryNote()
    {
        return $this->send_delivery_note;
    }

    /**
     * Set send_invoice
     *
     * @param boolean $sendInvoice
     * @return Contact
     */
    public function setSendInvoice($sendInvoice)
    {
        $this->send_invoice = $sendInvoice;
    
        return $this;
    }

    /**
     * Get send_invoice
     *
     * @return boolean 
     */
    public function getSendInvoice()
    {
        return $this->send_invoice;
    }

    /**
     * Set send_payment
     *
     * @param boolean $sendPayment
     * @return Contact
     */
    public function setSendPayment($sendPayment)
    {
        $this->send_payment = $sendPayment;
    
        return $this;
    }

    /**
     * Get send_payment
     *
     * @return boolean 
     */
    public function getSendPayment()
    {
        return $this->send_payment;
    }

    /**
     * Set customer
     *
     * @param \Teclliure\CustomerBundle\Entity\Customer $customer
     * @return Contact
     */
    public function setCustomer(\Teclliure\CustomerBundle\Entity\Customer $customer = null)
    {
        $this->customer = $customer;
    
        return $this;
    }

    /**
     * Get customer
     *
     * @return \Teclliure\CustomerBundle\Entity\Customer 
     */
    public function getCustomer()
    {
        return $this->customer;
    }
}