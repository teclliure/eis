<?php

namespace Teclliure\InvoiceBundle\Entity;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="quote")
 * @ORM\Entity
 */
class Quote {
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var string $number
     *
     * @ORM\Column(type="string", length=25, unique=true, nullable=true )
     *
     */
    private $number;

     /**
     *
     * Possible status are
     *  - DRAFT         - 0
     *  - PENDING       - 1
     *  - REJECTED      - 2
     *  - APPROVED      - 3
     *
     * @var integer $number
     *
     * @ORM\Column(type="smallint")
     *
     */
    private $status = 0;

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
     * Set number
     *
     * @param string $number
     * @return Quote
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
     * Set status
     *
     * @param integer $status
     * @return Quote
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
     * Set created
     *
     * @param \DateTime $created
     * @return Quote
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
     * @return Quote
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
     * Set common
     *
     * @param \Teclliure\InvoiceBundle\Entity\Common $common
     * @return Quote
     */
    public function setCommon(\Teclliure\InvoiceBundle\Entity\Common $common)
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
            return 'Pending';
        }
        elseif ($this->getStatus() == 2) {
            return 'Rejected';
        }
        elseif ($this->getStatus() == 3) {
            return 'Approved';
        }
    }

    /**
     * Set footnote
     *
     * @param string $footnote
     * @return Quote
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
     * @return Quote
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
     * @return Quote
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