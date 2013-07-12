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
use Symfony\Component\Security\Acl\Exception\Exception;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\InvoiceBundle\Entity\DeliveryNote;
use Teclliure\InvoiceBundle\Service\CommonService;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;


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
                        ->select('c, d')
                        ->from('TeclliureInvoiceBundle:Common','c')
                        ->innerJoin('c.delivery_note','d');

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

            foreach ($filters as $key=>$filter) {
                if ($filter) {
                    $fieldName = preg_replace('/^d_/', 'd.',preg_replace('/^c_/', 'c.', $key));
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
     * @param integer $commonId
     *
     * @return mixed Common or null
     *
     * @api 0.1
     */
    public function getDeliveryNote($commonId, $new = false) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c, d')
            ->from('TeclliureInvoiceBundle:Common','c')
            ->where('c.id = :commonId')
            ->setParameter('commonId', $commonId);

        if ($new) {
            $queryBuilder->leftJoin('c.delivery_note','d');
        }
        else {
            $queryBuilder->innerJoin('c.delivery_note','d');
        }

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Create deliveryNote
     *
     * @return Common
     *
     * @api 0.1
     */
    public function createDeliveryNote() {
        $common = new Common();
        $this->putDefaults($common);
        return $common;
    }

    public function putDefaults(Common $common) {
        $deliveryNote = $common->getDeliveryNote();
        if (!$deliveryNote) {
            $deliveryNote = new DeliveryNote();
        }

        if ($this->getConfig()->get('default_country') && !$common->getCustomerCountry()) {
            $common->setCustomerCountry($this->getConfig()->get('default_country'));
        }
        if ($this->getConfig()->get('default_footnote_order') && !$deliveryNote->getFootnote()) {
            $deliveryNote->setFootnote($this->getConfig()->get('default_footnote_order'));
        }
        $common->setDeliveryNote($deliveryNote);
    }

    /**
     * Save deliveryNote
     *
     * Save deliveryNote and calculate amounts
     *
     * @param Common $common DeliveryNote to save
     *
     * @api 0.1
     */
    public function saveDeliveryNote(Common $common, $originalLines = array()) {
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

        if (!$common->getDeliveryNote()) {
            throw new Exception('Common is not an order');
        }
        elseif ($common->getDeliveryNote()->getStatus() > 0) {
            throw new Exception('Only orders with status draft could be edited');
        }

        if (!$common->getDeliveryNote()->getNumber()) {
            // We get WRITE lock to avoid duplicated deliveryNote numbers
            $em = $this->getEntityManager();
            $em->getConnection()->exec('LOCK TABLE delivery_note d0_ WRITE;');
            if (!$common->getDeliveryNote()->getNumber()) {
                $nextDeliveryNoteNumber = $this->getNextDeliveryNoteNumber(new \DateTime());
                $common->getDeliveryNote()->setNumber($nextDeliveryNoteNumber);
            }
            $em->persist($common);
            $em->flush();
            $em->getConnection()->exec('UNLOCK TABLES;');
        }
        if ($common->getQuote() && $common->getQuote()->getStatus() != 3) {
            $common->getQuote()->setStatus(3);
        }
        $this->updateCustomerFromCommon($common);

        $em = $this->getEntityManager();
        // print count($common->getCommonLines());
        $em->persist($common);
        $em->flush();
    }

    /**
     * Close deliveryNote
     *
     * Set status to closed and generate deliveryNote number
     *
     * @param Common $common DeliveryNote to close
     *
     * @api 0.1
     */
    public function closeDeliveryNote(Common $common) {
        if ($common->getDeliveryNote()->getStatus() != 0) {
            throw new Exception('Only orders with status draft could be closed');
        }
        $common->getDeliveryNote()->setStatus(1);
        $em = $this->getEntityManager();
        $em->persist($common);
        $em->flush();
    }

    /**
     * Open order
     *
     * Set status to open
     *
     * @param Common $common DeliveryNote to open
     *
     * @api 0.1
     */
    public function openDeliveryNote(Common $common) {
        if (!($common->getDeliveryNote()->getStatus() > 0)) {
            throw new Exception('Only orders with status different than draft could be opened');
        }
        $common->getDeliveryNote()->setStatus(0);
        $em = $this->getEntityManager();
        $em->persist($common);
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
        $size = 6;
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