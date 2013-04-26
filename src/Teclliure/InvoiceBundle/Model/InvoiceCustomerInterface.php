<?php

namespace Teclliure\InvoiceBundle\Model;

/**
 * An interface that the invoice Customer object should implement.
 * In most circumstances, only a single object should implement
 * this interface as the ResolveTargetEntityListener can only
 * change the target to a single object.
 */

interface InvoiceCustomerInterface {
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
    public function getIdentification();

    /**
     * @return string
     */
    public function getAddress();

    /**
     * @return string
     */
    public function getZipCode();

    /**
     * @return string
     */
    public function getCity();

    /**
     * @return string
     */
    public function getState();

    /**
     * @return string
     */
    public function getCountry();
}