<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Teclliure\InvoiceBundle\Model\InvoiceCustomerInterface;

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
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min = 2, max = 10000)
     *
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Teclliure\InvoiceBundle\Model\InvoiceCustomerInterface",  cascade={"persist"}, inversedBy="commons")
     * @ORM\JoinColumn(name="common_id", referencedColumnName="id", onDelete="SET NULL")
     *
     * @var InvoiceCustomerInterface
     */
    protected $customer;

    /**
     * @ORM\Column(type="string", length=200)
     *
     * @Assert\Length(min = 4, max = 200)
     * @Assert\NotBlank()
     *
     */
    private $customer_name;

    /**
     * @ORM\Column(type="string", length=30)
     *
     * @Assert\Length(min = 5, max = 30)
     * @Assert\NotBlank()
     */
    private $customer_identification;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     *
     * @Assert\Length(min = 3, max = 10)
     *
     */
    private $customer_zip_code;

    /**
     * @ORM\Column(type="text", nullable=true)
     *
     * @Assert\Length(min = 5, max = 20000)
     *
     */
    private $customer_address;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 2, max = 200)
     *
     */
    private $customer_city;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Length(min = 2, max = 200)
     *
     */
    private $customer_state;

    /**
     * @ORM\Column(type="string", length=200, nullable=true)
     *
     * @Assert\Country
     *
     */
    private $customer_country;

     /**
     *
     * @ORM\ManyToMany(targetEntity="Teclliure\InvoiceBundle\Entity\CommonLine", inversedBy="common", cascade={"persist", "remove"})
     * @ORM\JoinTable(name="common_lines")
     */
    protected $common_lines;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->common_lines = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set customer_name
     *
     * @param string $customerName
     * @return Common
     */
    public function setCustomerName($customerName)
    {
        $this->customer_name = $customerName;
    
        return $this;
    }

    /**
     * Get customer_name
     *
     * @return string 
     */
    public function getCustomerName()
    {
        return $this->customer_name;
    }

    /**
     * Set customer_identification
     *
     * @param string $customerIdentification
     * @return Common
     */
    public function setCustomerIdentification($customerIdentification)
    {
        $this->customer_identification = $customerIdentification;
    
        return $this;
    }

    /**
     * Get customer_identification
     *
     * @return string 
     */
    public function getCustomerIdentification()
    {
        return $this->customer_identification;
    }

    /**
     * Set customer_zip_code
     *
     * @param string $customerZipCode
     * @return Common
     */
    public function setCustomerZipCode($customerZipCode)
    {
        $this->customer_zip_code = $customerZipCode;
    
        return $this;
    }

    /**
     * Get customer_zip_code
     *
     * @return string 
     */
    public function getCustomerZipCode()
    {
        return $this->customer_zip_code;
    }

    /**
     * Set customer_address
     *
     * @param string $customerAddress
     * @return Common
     */
    public function setCustomerAddress($customerAddress)
    {
        $this->customer_address = $customerAddress;
    
        return $this;
    }

    /**
     * Get customer_address
     *
     * @return string 
     */
    public function getCustomerAddress()
    {
        return $this->customer_address;
    }

    /**
     * Set customer_city
     *
     * @param string $customerCity
     * @return Common
     */
    public function setCustomerCity($customerCity)
    {
        $this->customer_city = $customerCity;
    
        return $this;
    }

    /**
     * Get customer_city
     *
     * @return string 
     */
    public function getCustomerCity()
    {
        return $this->customer_city;
    }

    /**
     * Set customer_state
     *
     * @param string $customerState
     * @return Common
     */
    public function setCustomerState($customerState)
    {
        $this->customer_state = $customerState;
    
        return $this;
    }

    /**
     * Get customer_state
     *
     * @return string 
     */
    public function getCustomerState()
    {
        return $this->customer_state;
    }

    /**
     * Set customer_country
     *
     * @param string $customerCountry
     * @return Common
     */
    public function setCustomerCountry($customerCountry)
    {
        $this->customer_country = $customerCountry;
    
        return $this;
    }

    /**
     * Get customer_country
     *
     * @return string 
     */
    public function getCustomerCountry()
    {
        return $this->customer_country;
    }


    /**
     * Set customer
     *
     * @param \Teclliure\CustomerBundle\Entity\Customer $customer
     * @return Common
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

    /**
     * Set description
     *
     * @param string $description
     * @return Common
     */
    public function setDescription($description)
    {
        $this->description = $description;
    
        return $this;
    }

    /**
     * Get description
     *
     * @return string 
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Update amount based con invoice lines
     *
     * @ORM\PrePersist()
     */
    public function doOnPrePersist()
    {
        /*if ($this->getInvoice()) {
            $this->getInvoice()->setBaseAmount($this->getInvoice()->calculateBaseAmount($this));
            $this->getInvoice()->setDiscountAmount($this->getInvoice()->calculateDiscountAmount($this));
            $this->getInvoice()->setNetAmount($this->getInvoice()->calculateNetAmount($this));
            $this->getInvoice()->setTaxAmount($this->getInvoice()->calculateTaxAmount($this));
            $this->getInvoice()->setGrossAmount($this->getInvoice()->calculateGrossAmount($this));
        }*/
    }

    public function getBaseAmount()
    {
        $amount = 0;
        $lines = $this->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getBaseAmount();
        }
        return round($amount,2);
    }

    public function getDiscountAmount()
    {
        $amount = 0;
        $lines = $this->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getDiscountAmount();
        }
        return round($amount,2);
    }

    public function getNetAmount()
    {
        $amount = 0;
        $lines = $this->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getNetAmount();
        }
        return round($amount,2);
    }

    public function getTaxAmount()
    {
        $amount = 0;
        $lines = $this->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getTaxAmount();
        }
        return round($amount,2);
    }

    public function getGrossAmount()
    {
        $amount = 0;
        $lines = $this->getCommonLines();
        foreach ($lines as $line) {
            $amount += $line->getTotalAmount();
        }
        return round($amount,2);
    }

    public function getTaxAmountArray()
    {
        $taxesArray = array();
        $lines = $this->getCommonLines();
        foreach ($lines as $line) {
            $taxes = $line->getTaxes();
            foreach ($taxes as $tax) {
                if (!isset($taxesArray[$tax->getId()])) {
                    $taxesArray[$tax->getId()]['tax'] = $tax;
                    $taxesArray[$tax->getId()]['amount'] = 0;
                }
                $taxesArray[$tax->getId()]['amount'] += round(($line->getNetAmount()*$tax->getValue())/100, 2);
            }
        }
        return $taxesArray;
    }

    /**
     * Add common_lines
     *
     * @param \Teclliure\InvoiceBundle\Entity\CommonLine $commonLines
     * @return Common
     */
    public function addCommonLine(\Teclliure\InvoiceBundle\Entity\CommonLine $commonLines)
    {
        $this->common_lines[] = $commonLines;
    
        return $this;
    }

    /**
     * Remove common_lines
     *
     * @param \Teclliure\InvoiceBundle\Entity\CommonLine $commonLines
     */
    public function removeCommonLine(\Teclliure\InvoiceBundle\Entity\CommonLine $commonLines)
    {
        $this->common_lines->removeElement($commonLines);
    }

    /**
     * Get common_lines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCommonLines()
    {
        return $this->common_lines;
    }
}