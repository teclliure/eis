<?php

namespace Teclliure\InvoiceBundle\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\CustomerBundle\Entity\Customer;
use Knp\Component\Pager\Paginator;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Craue\ConfigBundle\Util\Config;

/*
 * This file is part of Teclliure developed package build on 6/25/13.
 *
 * (c) Marc Montañés Abarca <marc@teclliure.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
/**
 * Invoice service. It "should" be the ONLY class used directly by controllers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class CommonService implements PaginatorAwareInterface {
    /**
     * EntityManager
     *
     * @var Object
     */
    protected $em;

    /**
     * Config
     *
     * @var Object
     */
    protected $config;

    /**
     * @var Paginator
     */
    private $paginator;

    /**
     * Constructor.
     *
     * @param EntityManager
     *
     */
    public function __construct(EntityManager $em, Config $config) {
        $this->em = $em;
        $this->config = $config;
    }

    /**
     * Return entity manager
     *
     * @return EntityManager
     *
     */
    public function getEntityManager() {
        return $this->em;
    }

    /**
     * Return config
     *
     * @return Config
     *
     */
    public function getConfig() {
        return $this->config;
    }

    /**
     * Sets the KnpPaginator instance.
     *
     * @param Paginator $paginator
     *
     * @return PaginatorAware
     */
    public function setPaginator(Paginator $paginator)
    {
        $this->paginator = $paginator;

        return $this;
    }

    /**
     * Returns the KnpPaginator instance.
     *
     * @return Paginator
     */
    public function getPaginator()
    {
        return $this->paginator;
    }

    /**
     * Create a new customer
     *
     * Create a new customer if the identification doesn't exists
     *
     * @param Customer
     *
     * @api 0.1
     */
    protected function updateCustomerFromCommon(Common $common) {
        // FIXME: Change this call to a CustomerService call and don't use Customer class directly
        $customer = $common->getCustomer();
        if (!$customer) {
            $customer = $this->getEntityManager()->getRepository('TeclliureCustomerBundle:Customer')->findOneBy(array('identification'=>$common->getCustomerIdentification()));
            if ($customer) {
                $common->setCustomer($customer);
            }
            else {
                $customer = new Customer();
                $customer->setIdentification($common->getCustomerIdentification());
                $customer->setName($common->getCustomerName());
                $customer->setAddress($common->getCustomerAddress());
                $customer->setZipCode($common->getCustomerZipCode());
                $customer->setCity($common->getCustomerCity());
                $customer->setState($common->getCustomerState());
                $customer->setCountry($common->getCustomerCountry());

                $common->setCustomer($customer);
            }
        }
        else {
            if (!$customer->getName()) {
                $customer->setName($common->getCustomerName());
            }
            if (!$customer->getAddress()) {
                $customer->setAddress($common->getCustomerAddress());
            }
            if (!$customer->getZipCode()) {
                $customer->setZipCode($common->getCustomerZipCode());
            }
            if (!$customer->getCity()) {
                $customer->setCity($common->getCustomerCity());
            }
            if (!$customer->getState()) {
                $customer->setState($common->getCustomerState());
            }
            if (!$customer->getCountry()) {
                $customer->setCountry($common->getCustomerCountry());
            }
        }
    }
}