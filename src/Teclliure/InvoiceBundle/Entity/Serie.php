<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="serie")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Serie {
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=200, unique=true)
     *
     * @Assert\Length(min = 2, max = 200)
     * @Assert\NotBlank()
     *
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=5, unique=true)
     *
     * @Assert\Length(min = 2, max = 5)
     * @Assert\NotBlank()
     *
     */
    private $short;

    /**
     * @ORM\Column(type="integer")
     *
     * @Assert\Type(type="integer")
     * @Assert\NotBlank()
     *
     */
    private $first_number = 1;

    /**
     * @ORM\OneToMany(targetEntity="Teclliure\InvoiceBundle\Entity\Invoice", mappedBy="serie")
     * @ORM\OrderBy({"number" = "ASC"})
     */
    private $invoices;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $active = true;

    /**
    *
    * var bool
    *
    */
    private $is_empty = false;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->invoices = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString() {
        return $this->getName();
    }

    /**
     * @param mixed $is_empty
     */
    public function setIsEmpty($is_empty)
    {
        $this->is_empty = $is_empty;
    }

    /**
     * @return mixed
     */
    public function getIsEmpty()
    {
        return $this->is_empty;
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
     * Set name
     *
     * @param string $name
     * @return Serie
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
     * Set short
     *
     * @param string $short
     * @return Serie
     */
    public function setShort($short)
    {
        $this->short = $short;
    
        return $this;
    }

    /**
     * Get short
     *
     * @return string 
     */
    public function getShort()
    {
        return $this->short;
    }

    /**
     * Set first_number
     *
     * @param integer $firstNumber
     * @return Serie
     */
    public function setFirstNumber($firstNumber)
    {
        $this->first_number = $firstNumber;
    
        return $this;
    }

    /**
     * Get first_number
     *
     * @return integer 
     */
    public function getFirstNumber()
    {
        return $this->first_number;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Serie
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
     * Add invoices
     *
     * @param \Teclliure\InvoiceBundle\Entity\Invoice $invoices
     * @return Serie
     */
    public function addInvoice(\Teclliure\InvoiceBundle\Entity\Invoice $invoices)
    {
        $this->invoices[] = $invoices;
    
        return $this;
    }

    /**
     * Remove invoices
     *
     * @param \Teclliure\InvoiceBundle\Entity\Invoice $invoices
     */
    public function removeInvoice(\Teclliure\InvoiceBundle\Entity\Invoice $invoices)
    {
        $this->invoices->removeElement($invoices);
    }

    /**
     * Get invoices
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getInvoices()
    {
        return $this->invoices;
    }
}