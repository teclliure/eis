<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Teclliure\InvoiceBundle\Entity\Common;

/**
 * @ORM\Table(name="common_line")
 * @ORM\Entity
 */
class CommonLine {
     /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

     /**
     * @var float $quantity
     *
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     *
     */
    private $quantity;

    /**
     * @var float $quantity
     *
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     *
     */
    private $unitary_cost;

     /**
     * @var integer $discount
     *
     * @ORM\Column(type="integer")
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="integer")
     *
     */
    private $discount = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Teclliure\InvoiceBundle\Entity\Common", inversedBy="common_lines")
     * @var Common
     */
    protected $common;

    /**
     * @ORM\Column(type="string", length=200)
     *
     * @Assert\Length(min = 2, max = 200)
     * @Assert\NotBlank()
     *
     */
    private $description;

    /**
     * @ORM\ManyToMany(targetEntity="Teclliure\InvoiceBundle\Entity\Tax", inversedBy="lines", cascade={"persist"})
     */
    private $taxes;

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
     * Set quantity
     *
     * @param float $quantity
     * @return CommonLine
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    
        return $this;
    }

    /**
     * Get quantity
     *
     * @return float 
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set unitary_cost
     *
     * @param float $unitaryCost
     * @return CommonLine
     */
    public function setUnitaryCost($unitaryCost)
    {
        $this->unitary_cost = $unitaryCost;
    
        return $this;
    }

    /**
     * Get unitary_cost
     *
     * @return float 
     */
    public function getUnitaryCost()
    {
        return $this->unitary_cost;
    }

    /**
     * Set discount
     *
     * @param integer $discount
     * @return CommonLine
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
    
        return $this;
    }

    /**
     * Get discount
     *
     * @return integer 
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Set common
     *
     * @param \Teclliure\InvoiceBundle\Entity\Common $common
     * @return CommonLine
     */
    public function setCommon(\Teclliure\InvoiceBundle\Entity\Common $common = null)
    {
        $this->common = $common;
    
        return $this;
    }

    /**
     * Get common
     *
     * @return \Teclliure\InvoiceBundle\Entity\Common 
     */
    public function getCommon()
    {
        return $this->common;
    }

    /**
     * Set description
     *
     * @param string $description
     * @return CommonLine
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
     * Constructor
     */
    public function __construct()
    {
        $this->taxes = new \Doctrine\Common\Collections\ArrayCollection();
    }
    

    /**
     * Get base amount
     *
     * @return float
     */
    public function getBaseAmount() {
        return $this->getQuantity()*$this->getUnitaryCost();
    }

    /**
     * Get discount
     *
     * @return float
     */
    public function getDiscountAmount() {
        return ($this->getBaseAmount()*$this->getDiscount())/100;
    }

    /**
     * Get discount
     *
     * @return float
     */
    public function getNetAmount() {
        return $this->getBaseAmount()-$this->getDiscountAmount();
    }

    /**
     * Get taxes
     *
     * @return float
     */
    public function getTaxAmount() {
        $taxTotal = 0;
        $taxes = $this->getTaxes();
        foreach ($taxes as $tax) {
            $taxTotal += ($this->getNetAmount()*$tax->getValue())/100;
        }
        return $taxTotal;
    }

    /**
     * Get base amount - discount + taxes
     *
     * @return float
     */
    public function getTotalAmount() {
        return $this->getNetAmount()+$this->getTaxAmount();
    }

    /**
     * Add taxes
     *
     * @param \Teclliure\InvoiceBundle\Entity\Tax $taxes
     * @return CommonLine
     */
    public function addTax(\Teclliure\InvoiceBundle\Entity\Tax $taxes)
    {
        $this->taxes[] = $taxes;

        return $this;
    }

    /**
     * Remove taxes
     *
     * @param \Teclliure\InvoiceBundle\Entity\Tax $taxes
     */
    public function removeTax(\Teclliure\InvoiceBundle\Entity\Tax $taxes)
    {
        $this->taxes->removeElement($taxes);
    }

    /**
     * Get taxes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getTaxes()
    {
        return $this->taxes;
    }
}
