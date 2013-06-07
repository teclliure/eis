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
     * Constructor.
     *
     * @param EntityManager
     *
     */
    public function __construct(EntityManager $em) {
        $this->em = $em;
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
    public function getInvoices($limit = 10, $offset = 0, $filter = array(), $order = array()) {
        //$query = $this->getEntityManager()->createQueryBuilder('SELECT c,i FROM TeclliureInvoiceBundle:Common c LEFT JOIN c.invoice i :where ORDER BY :order');
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('c, i')
                        ->from('TeclliureInvoiceBundle:Common','c')
                        ->leftJoin('c.invoice','i');

        if ($order) {
            foreach ($order as $key=>$ascDesc) {
                $queryBuilder->addOrderBy($key,$ascDesc);
            }
        }
        $queryBuilder->addOrderBy('i.issue_date', 'DESC');

        if ($filter) {

        }
        // $queryBuilder->setParameter('where', $where);
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
    public function saveInvoice(Common $common) {
        if ($common->getInvoice() && !$common->getInvoice()->getStatus()) {
            $common->getInvoice()->setBaseAmount($common->getInvoice()->calculateBaseAmount($common));
            $common->getInvoice()->setDiscountAmount($common->getInvoice()->calculateDiscountAmount($common));
            $common->getInvoice()->setNetAmount($common->getInvoice()->calculateNetAmount($common));
            $common->getInvoice()->setTaxAmount($common->getInvoice()->calculateTaxAmount($common));
            $common->getInvoice()->setGrossAmount($common->getInvoice()->calculateGrossAmount($common));
        }
        elseif (!$common->getInvoice()) {
            throw new Exception('Common is not an invoice');
        }
        else {
            throw new Exception('Only invoices with status draft could be edited');
        }
        $em = $this->getEntityManager();
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
}