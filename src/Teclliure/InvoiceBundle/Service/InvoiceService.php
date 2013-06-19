<?php
/**
 * This file is part of Teclliure developed package build on 2013.
 *
 * (c) Marc Montañés Abarca <marc@teclliure.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teclliure\InvoiceBundle\Service;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Acl\Exception\Exception;
use Symfony\Component\Validator\Constraints\DateTime;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\InvoiceBundle\Entity\Invoice;
use Teclliure\InvoiceBundle\Entity\Serie;
use Craue\ConfigBundle\Util\Config;
use Teclliure\CustomerBundle\Entity\Customer;


/**
 * Invoice service. It "should" be the ONLY class used directly by controllers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class InvoiceService {

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
     * Get invoices
     *
     * @param integer $limit
     * @param integer $offset
     * @param array   $filter
     * @param array   $order
     *
     * @return array
     *
     * @api 0.1
     */
    public function getInvoices($limit = 10, $offset = 0, $filters = array(), $order = array()) {
        //$query = $this->getEntityManager()->createQueryBuilder('SELECT c,i FROM TeclliureInvoiceBundle:Common c LEFT JOIN c.invoice i :where ORDER BY :order');
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('c, i')
                        ->from('TeclliureInvoiceBundle:Common','c')
                        ->leftJoin('c.invoice','i');

        if ($order) {
            foreach ($order as $key=>$ascDesc) {
                $queryBuilder->addOrderBy($key, $ascDesc);
            }
        }
        $queryBuilder->addOrderBy('i.issue_date', 'DESC');

        if ($filters) {
            if (isset($filters['search']) && $filters['search']) {
                $queryBuilder->where('i.number LIKE :search OR c.customer_name LIKE :search2')
                    ->setParameters(array(
                        'search'    => '%'.$filters['search'].'%',
                        'search2'   => '%'.$filters['search'].'%'
                ));
                unset ($filters['search']);
            }

            if (isset($filters['start_issue_date']) && $filters['start_issue_date']) {
                $queryBuilder->andWhere('i.issue_date >= :start_issue_date')
                    ->setParameter('start_issue_date', $filters['start_issue_date'],  \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['start_issue_date']);
            }
            if (isset($filters['end_issue_date']) && $filters['end_issue_date']) {
                $queryBuilder->andWhere('i.issue_date <= :end_issue_date')
                    ->setParameter('end_issue_date', $filters['end_issue_date'], \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['end_issue_date']);
            }

            foreach ($filters as $key=>$filter) {
                if ($filter) {
                    $fieldName = preg_replace('/^i_/', 'i.',preg_replace('/^c_/', 'c.', $key));
                    $value = $filter;
                    if (is_array($filter)) {
                        $queryBuilder->andWhere($fieldName.' IN (:where'.$key.')')
                            ->setParameter('where'.$key, $value);
                    }
                    else {
                        if (is_object($value)) {
                            $value = $value->getId();
                            $queryBuilder->andWhere($fieldName.' = :where'.$key)
                                ->setParameter('where'.$key, $value);
                        }
                        else {
                            $queryBuilder->andWhere($fieldName.' LIKE :where'.$key)
                                ->setParameter('where'.$key, '%'.$value.'%');
                        }
                    }
                }
            }
        }

        $queryBuilder->setMaxResults($limit);
        $queryBuilder->setFirstResult($offset);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get invoice
     *
     * @param integer $commonId
     *
     * @return mixed Common or null
     *
     * @api 0.1
     */
    public function getInvoice($commonId) {
        // $query = $this->getEntityManager()->createQueryBuilder('SELECT c,i FROM TeclliureInvoiceBundle:Common c LEFT JOIN c.invoice i :where ORDER BY :order');
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c, i')
            ->from('TeclliureInvoiceBundle:Common','c')
            ->innerJoin('c.invoice','i')
            ->where('c.id = :commonId')
            ->setParameter('commonId', $commonId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Create invoice
     *
     * @return Common
     *
     * @api 0.1
     */
    public function createInvoice() {
        $common = new Common();
        $issueDate = new \DateTime('now');
        $dueDate = new \DateTime('now');
        $invoice = new Invoice();
        $invoice->setIssueDate($issueDate);
        $invoice->setDueDate($dueDate->modify('+1 month'));
        if ($this->getConfig()->get('default_serie')) {
            $serie = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:Serie')->find($this->getConfig()->get('default_serie'));
            if ($serie) {
                $invoice->setSerie($serie);
            }
        }
        if ($this->getConfig()->get('default_country')) {
            $common->setCustomerCountry($this->getConfig()->get('default_country'));
        }
        $common->setInvoice($invoice);

        return $common;
    }

    /**
     * Save invoice
     *
     * Save invoice and calculate amounts
     *
     * @param Common $common Invoice to save
     *
     * @api 0.1
     */
    public function saveInvoice(Common $common, $originalLines = array()) {
        if ($originalLines)  {
            foreach ($common->getCommonLines() as $commonLine) {
                foreach ($originalLines as $key => $toDel) {
                    if ($toDel->getId() === $commonLine->getId()) {
                        unset($originalLines[$key]);
                    }
                }
            }

            // remove the relationship between the line and the common
            foreach ($originalLines as $line) {
                $this->getEntityManager()->remove($line);
            }
        }

        if ($common->getInvoice() && !$common->getInvoice()->getStatus()) {
            $common->getInvoice()->setBaseAmount($common->getBaseAmount());
            $common->getInvoice()->setDiscountAmount($common->getDiscountAmount());
            $common->getInvoice()->setNetAmount($common->getNetAmount());
            $common->getInvoice()->setTaxAmount($common->getTaxAmount());
            $common->getInvoice()->setGrossAmount($common->getGrossAmount());
        }
        elseif (!$common->getInvoice()) {
            throw new Exception('Common is not an invoice');
        }
        else {
            throw new Exception('Only invoices with status draft could be edited');
        }
        $this->updateCustomerFromCommon($common);

        $em = $this->getEntityManager();
        // print count($common->getCommonLines());
        $em->persist($common);
        $em->flush();
    }

    /**
     * Close invoice
     *
     * Set status to closed and generate invoice number
     *
     * @param Common $common Invoice to close
     *
     * @api 0.1
     */
    public function closeInvoice(Common $common) {
        if ($common->getInvoice()->getStatus() != 0) {
            throw new Exception('Only invoices with status draft could be closed');
        }
        $common->getInvoice()->setStatus(1);

        // We get WRITE lock to avoid duplicated invoice numbers
        $em = $this->getEntityManager();
        $em->getConnection()->exec('LOCK TABLE invoice i0_ WRITE;');
        if (!$common->getInvoice()->getNumber()) {
            $nextInvoiceNumber = $this->getNextInvoiceNumber($common->getInvoice()->getSerie(), $common->getInvoice()->getIssueDate());
            $common->getInvoice()->setNumber($nextInvoiceNumber);
        }
        $em->persist($common);
        $em->flush();
        $em->getConnection()->exec('UNLOCK TABLES;');
    }

    /**
     * Open invoice
     *
     * Set status to open
     *
     * @param Common $common Invoice to open
     *
     * @api 0.1
     */
    public function openInvoice(Common $common) {
        if (!($common->getInvoice()->getStatus() > 0)) {
            throw new Exception('Only invoices with status different than draft could be opened');
        }
        $common->getInvoice()->setStatus(0);
        $em = $this->getEntityManager();
        $em->persist($common);
        $em->flush();
    }

    /**
     * Get next invoice number
     *
     * Get next invoice number of a serie
     *
     * @param string Next invoice number
     *
     * @api 0.1
     */
    protected function getNextInvoiceNumber(Serie $serie = null, \DateTime $date) {
        $queryParams = array();

        // We have the year at first
        $size = 5;
        // Add serie prefix if needed
        if ($serie && $serie->getShort()) {
            $size = $size+strlen($serie->getShort())+1;
        }
        $selectSubstring = 'MAX(SUBSTRING(i.number, '.$size.')) as number';

        // Filter by date
        $queryParams['startDate'] = new \DateTime('@'.mktime (0, 0, 0, 1, 1, $date->format('Y')));
        $queryParams['endDate'] = new \DateTime('@'.mktime (0, 0, 0, 12, 32, $date->format('Y')));

        // Filter by serie
        $where = '';
        if ($serie) {
            $where = 'AND i.serie = '.$serie->getId();
        }

        $query = $this->em->createQuery('SELECT '.$selectSubstring.' FROM TeclliureInvoiceBundle:Invoice i
        WHERE i.issue_date >= :startDate AND i.issue_date < :endDate '.$where.' ORDER BY i.number desc');
        $query->setParameters($queryParams);
        $result = $query->getOneOrNullResult();
        // print_r ($result);
        if (!$result || !$result['number']) {
            if ($serie && $serie->getFirstNumber()) {
                $number = $serie->getFirstNumber();
            }
            else {
                $number = 1;
            }
        }
        else {
            // die($result['number']);
            $number = (int)$result['number']+1;
        }
        $number = $date->format('Y').str_pad($number, 8, '0', STR_PAD_LEFT);
        if ($serie && $serie->getShort()) {
            $number = $serie->getShort().$number;
        }
        // var_dump($result);
        // print_r ($result);
        // die ($result);

        return $number;
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
