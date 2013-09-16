<?php

namespace Teclliure\InvoiceBundle\Model;

/**
 * An interface that the invoice bundle contacts should implement.
 * In most circumstances, only a single object should implement
 * this interface as the ResolveTargetEntityListener can only
 * change the target to a single object.
 */

interface ContactInterface {
    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getEmail();

    /**
     * @return string
     */
    public function getPhone();
}