<?php

namespace Teclliure\CustomerBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Teclliure\InvoiceBundle\Model\InvoiceCustomerInterface;

/**
 * @ORM\Table(name="customer",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_legal_name", columns={"legal_name"}),
 *     @ORM\UniqueConstraint(name="unique_ident", columns={"identification"})
 * })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Customer implements InvoiceCustomerInterface {
    /**
    * @ORM\Id
    * @ORM\Column(type="integer")
    * @ORM\GeneratedValue
    */
    private $id;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 4, max = 200)
     *
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=200, unique=true)
     *
     * @Assert\Length(min = 4, max = 200)
     * @Assert\NotBlank()
     *
     */
    private $legal_name;

    /**
     * @ORM\Column(type="string", length=30, unique=true)
     *
     * @Assert\Length(min = 5, max = 30)
     * @Assert\NotBlank()
     */
    private $identification;

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
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 5, max = 200)
     * @Assert\Url()
     *
     */
    private $web;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     *
     * @Assert\Length(min = 3, max = 10)
     *
     */
    private $zip_code;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min = 5, max = 20000)
     *
     */
    private $address;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 2, max = 200)
     *
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 3, max = 200)
     *
     */
    private $state;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 2, max = 200)
     * @Assert\Country
     *
     */
    private $country;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\Length(min = 0   , max = 365)
     *
     */
    private $payment_period = 30;

    /**
     * @ORM\Column(type="integer", nullable=true)
     *
     * @Assert\Range(
     *      min = 1,
     *      max = 31,
     *      minMessage = "Day must be between 1 and 31",
     *      maxMessage = "Day must be between 1 and 31"
     * )
     *
     */
    private $payment_day;

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
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $active = true;

    /**
     * @ORM\OneToMany(targetEntity="Teclliure\InvoiceBundle\Entity\Common", mappedBy="customer")
     */
    protected $commons;

    /**
     * @ORM\OneToMany(targetEntity="Teclliure\CustomerBundle\Entity\Contact", mappedBy="customer", cascade={"persist", "remove"})
     */
    protected $contacts;

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
     * @return Customer
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
     * Set identification
     *
     * @param string $identification
     * @return Customer
     */
    public function setIdentification($identification)
    {
        $this->identification = $identification;
    
        return $this;
    }

    /**
     * Get identification
     *
     * @return string 
     */
    public function getIdentification()
    {
        return $this->identification;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Customer
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
     * @return Customer
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
     * Set zip_code
     *
     * @param string $zipCode
     * @return Customer
     */
    public function setZipCode($zipCode)
    {
        $this->zip_code = $zipCode;
    
        return $this;
    }

    /**
     * Get zip_code
     *
     * @return string 
     */
    public function getZipCode()
    {
        return $this->zip_code;
    }

    /**
     * Set address
     *
     * @param string $address
     * @return Customer
     */
    public function setAddress($address)
    {
        $this->address = $address;
    
        return $this;
    }

    /**
     * Get address
     *
     * @return string 
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Customer
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set state
     *
     * @param string $state
     * @return Customer
     */
    public function setState($state)
    {
        $this->state = $state;
    
        return $this;
    }

    /**
     * Get state
     *
     * @return string 
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Customer
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Customer
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
     * @return Customer
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
     * Set active
     *
     * @param boolean $active
     * @return Customer
     */
    public function setActive($active)
    {
        $this->active = $active;
    
        return $this;
    }

    /**
     * Get active
     *
     * @return boolean 
     */
    public function getActive()
    {
        return $this->active;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->commons = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set web
     *
     * @param string $web
     * @return Customer
     */
    public function setWeb($web)
    {
        $this->web = $web;
    
        return $this;
    }

    /**
     * Get web
     *
     * @return string 
     */
    public function getWeb()
    {
        return $this->web;
    }

    /**
     * Add commons
     *
     * @param \Teclliure\InvoiceBundle\Entity\Common $commons
     * @return Customer
     */
    public function addCommon(\Teclliure\InvoiceBundle\Entity\Common $commons)
    {
        $this->commons[] = $commons;
    
        return $this;
    }

    /**
     * Remove commons
     *
     * @param \Teclliure\InvoiceBundle\Entity\Common $commons
     */
    public function removeCommon(\Teclliure\InvoiceBundle\Entity\Common $commons)
    {
        $this->commons->removeElement($commons);
    }

    /**
     * Get commons
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommons()
    {
        return $this->commons;
    }

    /**
     * Set payment_period
     *
     * @param integer $paymentPeriod
     * @return Customer
     */
    public function setPaymentPeriod($paymentPeriod)
    {
        $this->payment_period = $paymentPeriod;
    
        return $this;
    }

    /**
     * Get payment_period
     *
     * @return integer 
     */
    public function getPaymentPeriod()
    {
        return $this->payment_period;
    }

    /**
     * Set payment_day
     *
     * @param integer $paymentDay
     * @return Customer
     */
    public function setPaymentDay($paymentDay)
    {
        $this->payment_day = $paymentDay;
    
        return $this;
    }

    /**
     * Get payment_day
     *
     * @return integer 
     */
    public function getPaymentDay()
    {
        return $this->payment_day;
    }

    /**
     * Add contacts
     *
     * @param \Teclliure\CustomerBundle\Entity\Contact $contacts
     * @return Customer
     */
    public function addContact(\Teclliure\CustomerBundle\Entity\Contact $contacts)
    {
        $contacts->setCustomer($this);
        $this->contacts[] = $contacts;
    
        return $this;
    }

    /**
     * Remove contacts
     *
     * @param \Teclliure\CustomerBundle\Entity\Contact $contacts
     */
    public function removeContact(\Teclliure\CustomerBundle\Entity\Contact $contacts)
    {
        $this->contacts->removeElement($contacts);
    }

    /**
     * Get contacts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getContacts()
    {
        return $this->contacts;
    }

    /**
     * Set legal_name
     *
     * @param string $legalName
     * @return Customer
     */
    public function setLegalName($legalName)
    {
        $this->legal_name = $legalName;
    
        return $this;
    }

    /**
     * Get legal_name
     *
     * @return string 
     */
    public function getLegalName()
    {
        return $this->legal_name;
    }
}