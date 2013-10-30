<?php

namespace Teclliure\InvoiceBundle\Util;

use Doctrine\ORM\EntityManager;

class DoctrineCustomChecker
{
    /**
     * @var Array
     */
    private $cachedMetadata;

    /**
     * Entity Manager
     *
     * @var Object
     */
    protected $em;


    /**
     * Constructor.
     *
     * @param EntityManager
     *
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    /**
     *
     * Checks if a field exists in a table
     *
     * @param $table Tablename
     * @param $field Field searched
     *
     * @throws \ErrorException
     */
    public function checkTableFieldExists($table, $field) {
        try {
            if (!isset($this->cachedMetadata[$table])) {
                $this->cachedMetadata[$table] = $this->em->getClassMetadata($table);

            }
            $classMetadata = $this->cachedMetadata[$table];
            if ($classMetadata->hasField($field) || $classMetadata->hasAssociation($field)) {
                return;
            }
            throw new \ErrorException('Trying to make an SQL injection ? Field '.$field.' does not exist in '.$table);
        }
        catch (Exception $e) {
            throw new \ErrorException('2: Trying to make an SQL injection '.$field.' in '.$table);
        }
    }
}