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

}