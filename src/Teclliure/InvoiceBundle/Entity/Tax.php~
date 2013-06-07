<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 *  @ORM\Table(name="tax",uniqueConstraints={
 *     @ORM\UniqueConstraint(name="unique_name", columns={"name"}),
 * })
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity("name")
 */
class Tax {
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
     * @var float $value
     *
     * @ORM\Column(type="float")
     *
     * @Assert\NotBlank()
     * @Assert\Type(type="float")
     *
     */
    private $value;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $active = true;

    /**
     * @ORM\Column(type="boolean")
     *
     * @Assert\Type(type="bool")
     *
     */
    private $is_default = false;

    /**
     * @ORM\ManyToMany(targetEntity="Teclliure\InvoiceBundle\Entity\CommonLine", mappedBy="taxes")
     */
    private $lines;

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
     * Get string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Tax
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
     * Set value
     *
     * @param float $value
     * @return Tax
     */
    public function setValue($value)
    {
        $this->value = $value;
    
        return $this;
    }

    /**
     * Get value
     *
     * @return float 
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set active
     *
     * @param boolean $active
     * @return Tax
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
     * Set is_default
     *
     * @param boolean $isDefault
     * @return Tax
     */
    public function setIsDefault($isDefault)
    {
        $this->is_default = $isDefault;
    
        return $this;
    }

    /**
     * Get is_default
     *
     * @return boolean 
     */
    public function getIsDefault()
    {
        return $this->is_default;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->lines = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add lines
     *
     * @param \Teclliure\InvoiceBundle\Entity\Tax $lines
     * @return Tax
     */
    public function addLine(\Teclliure\InvoiceBundle\Entity\Tax $lines)
    {
        $this->lines[] = $lines;

        return $this;
    }

    /**
     * Remove lines
     *
     * @param \Teclliure\InvoiceBundle\Entity\Tax $lines
     */
    public function removeLine(\Teclliure\InvoiceBundle\Entity\Tax $lines)
    {
        $this->lines->removeElement($lines);
    }

    /**
     * Get lines
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getLines()
    {
        return $this->lines;
    }
}
