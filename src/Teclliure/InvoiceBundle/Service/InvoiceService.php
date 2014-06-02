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

use Symfony\Component\Security\Acl\Exception\Exception;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\InvoiceBundle\Entity\Invoice;
use Teclliure\InvoiceBundle\Entity\Serie;
use Teclliure\InvoiceBundle\Service\CommonService;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Teclliure\InvoiceBundle\Event\CommonEvent;
use Teclliure\InvoiceBundle\CommonEvents;
use Teclliure\InvoiceBundle\Event\InvoiceEvent;

/**
 * Invoice service. It "should" be the ONLY class used directly by controllers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class InvoiceService extends CommonService implements PaginatorAwareInterface {
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
    public function getInvoices($limit = 10, $page = 1, $filters = array(), $sort = null) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('i, c')
                        ->from('TeclliureInvoiceBundle:Invoice','i')
                        ->innerJoin('i.common','c');

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
            if (isset($filters['i_id']) && $filters['i_id']) {
                $queryBuilder->andWhere('i.id = :id')
                    ->setParameter('id', $filters['i_id']);
                unset ($filters['i_id']);
            }

            foreach ($filters as $key=>$filter) {
                if ($filter) {
                    $fieldName = preg_replace('/^i_/', 'i.',preg_replace('/^c_/', 'c.', $key));
                    if (strpos($fieldName, 'c.') !== false) {
                        $this->getDoctrineCustomChecker()->checkTableFieldExists('TeclliureInvoiceBundle:Common',  preg_replace('/^c./', '',$fieldName));
                    }
                    else {
                        $this->getDoctrineCustomChecker()->checkTableFieldExists('TeclliureInvoiceBundle:Invoice',  preg_replace('/^i./', '',$fieldName));
                    }
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
        if ($sort && is_array($sort)) {
            foreach ($sort as $sortItem) {
                $queryBuilder->addOrderBy($sortItem['sort'], $sortItem['sortOrder']);
            }
        }
        $queryBuilder->addOrderBy('i.issue_date', 'DESC');
        $query = $queryBuilder->getQuery();

        if ($limit && $page) {
            $result = $this->getPaginator()->paginate(
                $query,
                $page,
                $limit
            );
        }
        else {
            $result = $query->getResult();
        }

        return $result;
    }

    /**
     * Get invoice
     *
     * @param integer $invoiceId
     *
     * @return mixed Invoice or null
     *
     * @api 0.1
     */
    public function getInvoice($invoiceId) {
        // $query = $this->getEntityManager()->createQueryBuilder('SELECT c,i FROM TeclliureInvoiceBundle:Invoice c LEFT JOIN c.invoice i :where ORDER BY :order');

        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c, i')
            ->from('TeclliureInvoiceBundle:Invoice','i')
            ->where('i.id = :invoiceId')
            ->setParameter('invoiceId', $invoiceId);

        $queryBuilder->innerJoin('i.common','c');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Get invoices only
     *
     * @param integer   $id
     * @param string    $type
     *
     * @return DoctrineCollection Invoices
     *
     * @api 0.1
     */
    public function getInvoicesView($limit = 10, $page = 1, $id, $type = null, $searchData = array()) {
        if ($type == 'deliveryNote') {
            $deliveryNote = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:DeliveryNote')->find($id);
            $searchData ['i_related_delivery_note'] = $deliveryNote;
        }
        else if ($type == 'quote') {
            $quote = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:Quote')->find($id);
            $searchData ['i_related_quote'] = $quote;
        }
        else {
            $searchData ['i_id'] = $id;
        }
        $invoices = $this->getInvoices($limit, $page, $searchData);

        return $invoices;
    }

    /**
     * Create invoice
     *
     * @return Common
     *
     * @api 0.1
     */
    public function createInvoice() {
        $invoice = new Invoice();
        $this->putDefaults($invoice);

        return $invoice;
    }

    public function putDefaults(Invoice $invoice) {
        $common = $invoice->getCommon();
        if (!$common) {
            $common = new Common();
        }

        if (!$invoice->getIssueDate()) {
            $issueDate = new \DateTime('now');
            $invoice->setIssueDate($issueDate);
        }

        if (!$invoice->getDueDate()) {
            $dueDate = new \DateTime('now');
            $invoice->setDueDate($dueDate->modify('+1 month'));
        }

        if (!$invoice->getSerie()) {
            if ($this->getConfig()->get('default_serie')) {
                $serie = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:Serie')->find($this->getConfig()->get('default_serie'));
                if ($serie) {
                    $invoice->setSerie($serie);
                }
            }
        }

        if (!$common->getCustomerCountry()) {
            if ($this->getConfig()->get('default_country')) {
                $common->setCustomerCountry($this->getConfig()->get('default_country'));
            }
        }

        if ($this->getConfig()->get('default_footnote_invoice') && !$invoice->getFootnote()) {
            $invoice->setFootnote($this->getConfig()->get('default_footnote_invoice'));
        }

        if (!$invoice->getCommon()) {
            $invoice->setCommon($common);
        }

        return $invoice;
    }

    /**
     * Save invoice
     *
     * Save invoice and calculate amounts
     *
     * @param Invoice $invoice Invoice to save
     *
     * @api 0.1
     */
    public function saveInvoice(Invoice $invoice, $originalLines = array(), $relatedQuote = null, $relatedDeliveryNote = null) {
        if ($originalLines)  {
            foreach ($invoice->getCommon()->getCommonLines() as $commonLine) {
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

        if ($invoice && !$invoice->getStatus()) {
            $invoice->setBaseAmount($invoice->getCommon()->getBaseAmount());
            $invoice->setDiscountAmount($invoice->getCommon()->getDiscountAmount());
            $invoice->setNetAmount($invoice->getCommon()->getNetAmount());
            $invoice->setTaxAmount($invoice->getCommon()->getTaxAmount());
            $invoice->setGrossAmount($invoice->getCommon()->getGrossAmount());
            $invoice->setDueAmount($invoice->getCommon()->getGrossAmount()-$invoice->getPaidAmount());
        }
        else {
            throw new Exception('Only invoices with status draft could be edited');
        }

        if ($relatedQuote) {
            $quote = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:Quote')->find($relatedQuote);
            $invoice->setRelatedQuote($quote);
        }
        if ($relatedDeliveryNote) {
            $deliveryNote = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:DeliveryNote')->find($relatedDeliveryNote);
            $invoice->setRelatedDeliveryNote($deliveryNote);
        }

        // Dispatch Event
        $preSaveEvent = $this->getEventDispatcher()->dispatch(CommonEvents::INVOICE_PRE_SAVED, new InvoiceEvent($invoice));

        $em = $this->getEntityManager();
        $em->persist($invoice);
        $em->flush();

        // Dispatch Event
        $saveEvent = $this->getEventDispatcher()->dispatch(CommonEvents::INVOICE_SAVED, new InvoiceEvent($invoice));

        /*if ($saveEvent->isPropagationStopped()) {
            // Things to do if stopped
        } else {
            // Things to do if not stopped
        }*/
    }

    /**
     * Close invoice
     *
     * Set status to closed and generate invoice number
     *
     * @param Common $invoice Invoice to close
     *
     * @api 0.1
     */
    public function closeInvoice(Invoice $invoice) {
        if ($invoice->getStatus() != 0) {
            throw new Exception('Only invoices with status draft could be closed');
        }
        $invoice->setStatus(1);

        // We get WRITE lock to avoid duplicated invoice numbers
        $em = $this->getEntityManager();
        $em->getConnection()->exec('LOCK TABLE invoice i0_ WRITE;');
        if (!$invoice->getNumber()) {
            $nextInvoiceNumber = $this->getNextInvoiceNumber($invoice->getSerie(), $invoice->getIssueDate());
            $invoice->setNumber($nextInvoiceNumber);
        }
        $em->persist($invoice);
        $em->flush();
        $em->getConnection()->exec('UNLOCK TABLES;');

        // Dispatch Event
        $closeEvent = new CommonEvent($invoice->getCommon());
        $closeEvent = $this->getEventDispatcher()->dispatch(CommonEvents::INVOICE_CLOSED, $closeEvent);

        if ($closeEvent->isPropagationStopped()) {
            // Things to do if stopped
        } else {
            // Things to do if not stopped
        }
    }

    /**
     * Open invoice
     *
     * Set status to open
     *
     * @param Invoice $invoice Invoice to open
     *
     * @api 0.1
     */
    public function openInvoice(Invoice $invoice) {
        if (!($invoice->getStatus() > 0)) {
            throw new Exception('Only invoices with status different than draft could be opened');
        }
        $invoice->setStatus(0);
        $em = $this->getEntityManager();
        $em->persist($invoice);
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
        else {
            $where = 'AND i.serie IS NULL';
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
            $number = (int)$result['number']+1;
        }
        $number = $date->format('Y').str_pad($number, 8, '0', STR_PAD_LEFT);
        if ($serie && $serie->getShort()) {
            $number = $serie->getShort().$number;
        }

        return $number;
    }
}
