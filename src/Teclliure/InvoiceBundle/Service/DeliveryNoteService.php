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
use Teclliure\InvoiceBundle\Entity\DeliveryNote;
use Teclliure\InvoiceBundle\Service\CommonService;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Teclliure\InvoiceBundle\Event\CommonEvent;
use Teclliure\InvoiceBundle\Event\DeliveryNoteEvent;
use Teclliure\InvoiceBundle\CommonEvents;

/**
 * DeliveryNote service. It "should" be the ONLY class used directly by controllers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class DeliveryNoteService extends CommonService implements PaginatorAwareInterface {
    /**
     * Get orders
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
    public function getDeliveryNotes($limit = 10, $page = 1, $filters = array()) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('d, c')
                        ->from('TeclliureInvoiceBundle:DeliveryNote','d')
                        ->innerJoin('d.common','c');

        if ($filters) {
            if (isset($filters['search']) && $filters['search']) {
                $queryBuilder->where('d.number LIKE :search OR c.customer_name LIKE :search2')
                    ->setParameters(array(
                        'search'    => '%'.$filters['search'].'%',
                        'search2'   => '%'.$filters['search'].'%'
                ));
                unset ($filters['search']);
            }

            if (isset($filters['start_issue_date']) && $filters['start_issue_date']) {
                $queryBuilder->andWhere('d.created >= :start_issue_date')
                    ->setParameter('start_issue_date', $filters['start_issue_date'],  \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['start_issue_date']);
            }
            if (isset($filters['end_issue_date']) && $filters['end_issue_date']) {
                $queryBuilder->andWhere('d.created <= :end_issue_date')
                    ->setParameter('end_issue_date', $filters['end_issue_date'], \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['end_issue_date']);
            }
            if (isset($filters['d_id']) && $filters['d_id']) {
                $queryBuilder->andWhere('d.id = :id')
                    ->setParameter('id', $filters['d_id']);
                unset ($filters['d_id']);
            }


            foreach ($filters as $key=>$filter) {
                if ($filter) {
                    $fieldName = preg_replace('/^d_/', 'd.',preg_replace('/^c_/', 'c.', $key));
                    if (strpos($fieldName, 'c.') !== false) {
                        $this->getDoctrineCustomChecker()->checkTableFieldExists('TeclliureInvoiceBundle:Common', preg_replace('/^c./', '', $fieldName));
                    }
                    else {
                        $this->getDoctrineCustomChecker()->checkTableFieldExists('TeclliureInvoiceBundle:DeliveryNote', preg_replace('/^d./', '', $fieldName));
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
        $queryBuilder->addOrderBy('d.created', 'DESC');
        $query = $queryBuilder->getQuery();

        $pagination = $this->getPaginator()->paginate(
            $query,
            $page,
            $limit
        );

        return $pagination;
    }

    /**
     * Get order
     *
     * @param integer $deliveryNoteId
     *
     * @return mixed DeliveryNote or null
     *
     * @api 0.1
     */
    public function getDeliveryNote($deliveryNoteId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c, d')
            ->from('TeclliureInvoiceBundle:DeliveryNote','d')
            ->where('d.id = :deliveryNoteId')
            ->setParameter('deliveryNoteId', $deliveryNoteId);

        $queryBuilder->innerJoin('d.common','c');

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Get delivery notes only
     *
     * @param integer   $id
     * @param string    $type
     *
     * @return DoctrineCollection DeliveryNotes
     *
     * @api 0.1
     */
    public function getDeliveryNotesView($limit = 10, $page = 1, $id, $type = null, $searchData = array()) {
        if ($type == 'quote') {
            $quote = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:Quote')->find($id);
            $searchData = array_merge(array('d_related_quote'=>$quote), $searchData);
        }
        else {
            $searchData = array_merge(array('d_id'=>$id), $searchData);
        }
        $invoices = $this->getDeliveryNotes($limit, $page, $searchData);

        return $invoices;
    }

    /**
     * Create deliveryNote
     *
     * @return DeliveryNote
     *
     * @api 0.1
     */
    public function createDeliveryNote() {
        $deliveryNote = new DeliveryNote();
        $this->putDefaults($deliveryNote);
        return $deliveryNote;
    }

    public function putDefaults(DeliveryNote $deliveryNote) {
        $common = $deliveryNote->getCommon();
        if (!$common) {
            $common = new Common();
        }
        if ($this->getConfig()->get('default_country') && !$common->getCustomerCountry()) {
            $common->setCustomerCountry($this->getConfig()->get('default_country'));
        }
        if ($this->getConfig()->get('default_footnote_order') && !$deliveryNote->getFootnote()) {
            $deliveryNote->setFootnote($this->getConfig()->get('default_footnote_order'));
        }
        $deliveryNote->setCommon($common);
    }

    /**
     * Save deliveryNote
     *
     * Save deliveryNote and calculate amounts
     *
     * @param DeliveryNote $deliveryNote DeliveryNote to save
     *
     * @api 0.1
     */
    public function saveDeliveryNote(DeliveryNote $deliveryNote, $originalLines = array(), $relatedQuote = null) {
        if ($originalLines)  {
            foreach ($deliveryNote->getCommon()->getCommonLines() as $commonLine) {
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

        if ($deliveryNote->getStatus() > 0) {
            throw new Exception('Only orders with status draft could be edited');
        }

        if (!$deliveryNote->getNumber()) {
            // We get WRITE lock to avoid duplicated deliveryNote numbers
            $em = $this->getEntityManager();
            $em->getConnection()->exec('LOCK TABLE delivery_note d0_ WRITE;');
            if (!$deliveryNote->getNumber()) {
                $nextDeliveryNoteNumber = $this->getNextDeliveryNoteNumber(new \DateTime());
                $deliveryNote->setNumber($nextDeliveryNoteNumber);
            }
            $em->persist($deliveryNote);
            $em->flush();
            $em->getConnection()->exec('UNLOCK TABLES;');
        }

        // TODO: Change Status with events
        /*if ($common->getQuote() && $common->getQuote()->getStatus() < 3) {
            $common->getQuote()->setStatus(3);
        }
        */
        if ($relatedQuote) {
            $quote = $this->getEntityManager()->getRepository('TeclliureInvoiceBundle:Quote')->find($relatedQuote);
            $deliveryNote->setRelatedQuote($quote);
        }
        $this->updateCustomerFromCommon($deliveryNote->getCommon());

        $em = $this->getEntityManager();
        $em->persist($deliveryNote);
        $em->flush();

        // Dispatch Event
        $closeEvent = new DeliveryNoteEvent($deliveryNote);
        $closeEvent = $this->getEventDispatcher()->dispatch(CommonEvents::DELIVERY_NOTE_SAVED, $closeEvent);

        if ($closeEvent->isPropagationStopped()) {
            // Things to do if stopped
        } else {
            // Things to do if not stopped
        }
    }

    /**
     * Create invoice from delivery note
     *
     * @param DeliveryNote $deliveryNote Delivery note to invoice
     * @return Invoice $invoice
     *
     * @api 0.1
     */
    public function createInvoiceFromDeliveryNote (DeliveryNote $deliveryNote) {
        if ($deliveryNote->getStatus() != 1 && $deliveryNote->getStatus() != 3) {
            throw new Exception('Only orders with status closed or partly invoiced could be invoiced');
        }
        $invoice = new Invoice();
        $common = clone ($deliveryNote->getCommon());
        $invoice->setCommon($common);
        // $invoice->setRelatedQuote($quote); - We set up in controller
        return $invoice;
    }

    /**
     * Close deliveryNote
     *
     * Set status to closed and generate deliveryNote number
     *
     * @param DeliveryNote $deliveryNote DeliveryNote to close
     *
     * @api 0.1
     */
    public function closeDeliveryNote(DeliveryNote $deliveryNote) {
        if ($deliveryNote->getStatus() != 0) {
            throw new Exception('Only orders with status draft could be closed');
        }
        $deliveryNote->setStatus(1);
        $em = $this->getEntityManager();
        $em->persist($deliveryNote);
        $em->flush();

        // Dispatch Event
        $closeEvent = new CommonEvent($deliveryNote->getCommon());
        $closeEvent = $this->getEventDispatcher()->dispatch(CommonEvents::DELIVERY_NOTE_CLOSED, $closeEvent);

        if ($closeEvent->isPropagationStopped()) {
            // Things to do if stopped
        } else {
            // Things to do if not stopped
        }
    }

    /**
     * Open order
     *
     * Set status to open
     *
     * @param DeliveryNote $deliveryNote DeliveryNote to open
     *
     * @api 0.1
     */
    public function openDeliveryNote(DeliveryNote $deliveryNote) {
        if (!($deliveryNote->getStatus() > 0)) {
            throw new Exception('Only orders with status different than draft could be opened');
        }
        $deliveryNote->setStatus(0);
        $em = $this->getEntityManager();
        $em->persist($deliveryNote);
        $em->flush();
    }

    /**
     * Get next order number
     *
     * Get next order number of a serie
     *
     * @param string Next delivery_note number
     *
     * @api 0.1
     */
    protected function getNextDeliveryNoteNumber(\DateTime $date) {
        $queryParams = array();

        // We have the year at first
        $size = 7;
        $selectSubstring = 'MAX(SUBSTRING(d.number, '.$size.')) as number';

        // Filter by date
        $queryParams['startDate'] = new \DateTime('@'.mktime (0, 0, 0, 1, 1, $date->format('Y')));
        $queryParams['endDate'] = new \DateTime('@'.mktime (0, 0, 0, 12, 32, $date->format('Y')));


        $query = $this->em->createQuery('SELECT '.$selectSubstring.' FROM TeclliureInvoiceBundle:DeliveryNote d
        WHERE d.created >= :startDate AND d.created < :endDate ORDER BY d.number desc');
        $query->setParameters($queryParams);
        $result = $query->getOneOrNullResult();
        // print_r ($result);
        if (!$result || !$result['number']) {
            $number = 1;
        }
        else {
            $number = (int)$result['number']+1;
        }
        $number = 'DN'.$date->format('Y').str_pad($number, 8, '0', STR_PAD_LEFT);

        return $number;
    }
}