<?php

namespace Craue\ConfigBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Christian Raue <christian.raue@gmail.com>
 * @copyright 2011-2013 Christian Raue
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 *
 * @ORM\Entity
 * @ORM\Table(name="craue_config_setting")
 */
class Setting {

	/**
	 * @var string
	 * @ORM\Id
	 * @ORM\Column(name="name", type="string", nullable=false, unique=true)
	 * @Assert\NotBlank
	 */
	protected $name;

	/**
	 * @var string
	 * @ORM\Column(name="value", type="text", nullable=true)
	 */
	protected $value;

    /**
     *
     * Possible types:
     *
     * text
     * textarea
     * choice
     * entity_object
     *
     * http://symfony.com/doc/current/reference/forms/types.html
     *
     * @var string
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    protected $type;

    /**
     *
     * serialized param string
     *
     * @var string
     * @ORM\Column(name="type_options", type="text", nullable=true)
     */
    protected $type_options;

	/**
	 * @var string
	 * @ORM\Column(name="section", type="string", nullable=true)
	 */
	protected $section;

    /**
     * @var integer
     * @ORM\Column(type="integer")
     */
    private $sort_order = 0;

	public function setName($name) {
		$this->name = $name;
	}

	public function getName() {
		return $this->name;
	}

	public function setValue($value) {
		$this->value = serialize($value);
	}

	public function getValue() {
        if ($this->getType() == 'entity') {
            $entity = unserialize($this->value);
            if (is_object($entity)) {
                return $entity->getId();
            }
            else return $entity;
        }
        if ($this->value) return unserialize($this->value);
        else return $this->value;
	}

	public function setSection($section) {
		$this->section = $section;
	}

	public function getSection() {
		return $this->section;
	}


    /**
     * Set type
     *
     * @param string $type
     * @return Setting
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type_options
     *
     * @param string $typeOptions
     * @return Setting
     */
    public function setTypeOptions($typeOptions)
    {
        $this->type_options = serialize($typeOptions);

        return $this;
    }

    /**
     * Get type_options
     *
     * @return string 
     */
    public function getTypeOptions()
    {
        return unserialize($this->type_options);
    }

    /**
     * Set sort_order
     *
     * @param integer $sortOrder
     * @return Setting
     */
    public function setSortOrder($sortOrder)
    {
        $this->sort_order = $sortOrder;

        return $this;
    }

    /**
     * Get sort_order
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sort_order;
    }
}
