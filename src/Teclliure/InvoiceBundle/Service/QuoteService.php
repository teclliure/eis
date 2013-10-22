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
use Teclliure\InvoiceBundle\Entity\Quote;
use Teclliure\InvoiceBundle\Service\CommonService;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Teclliure\InvoiceBundle\Event\CommonEvent;
use Teclliure\InvoiceBundle\CommonEvents;


/**
 * Quote service. It "should" be the ONLY class used directly by controllers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class QuoteService extends CommonService implements PaginatorAwareInterface {
    /**
     * Get quotes
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
    public function getQuotes($limit = 10, $page = 1, $filters = array()) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('c, q')
                        ->from('TeclliureInvoiceBundle:Common','c')
                        ->innerJoin('c.quote','q');

        if ($filters) {
            if (isset($filters['search']) && $filters['search']) {
                $queryBuilder->where('q.number LIKE :search OR c.customer_name LIKE :search2')
                    ->setParameters(array(
                        'search'    => '%'.$filters['search'].'%',
                        'search2'   => '%'.$filters['search'].'%'
                ));
                unset ($filters['search']);
            }

            if (isset($filters['start_issue_date']) && $filters['start_issue_date']) {
                $queryBuilder->andWhere('q.created >= :start_issue_date')
                    ->setParameter('start_issue_date', $filters['start_issue_date'],  \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['start_issue_date']);
            }
            if (isset($filters['end_issue_date']) && $filters['end_issue_date']) {
                $queryBuilder->andWhere('q.created <= :end_issue_date')
                    ->setParameter('end_issue_date', $filters['end_issue_date'], \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['end_issue_date']);
            }

            foreach ($filters as $key=>$filter) {
                if ($filter) {
                    $fieldName = preg_replace('/^q_/', 'q.',preg_replace('/^c_/', 'c.', $key));
                    if (strpos($fieldName, 'c.') !== false) {
                        $this->getDoctrineCustomChecker()->checkTableFieldExists('TeclliureInvoiceBundle:Common',  preg_replace('/^c./', '',$fieldName));
                    }
                    else {
                        $this->getDoctrineCustomChecker()->checkTableFieldExists('TeclliureInvoiceBundle:Quote',  preg_replace('/^q./', '',$fieldName));
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
        $queryBuilder->addOrderBy('q.created', 'DESC');
        $query = $queryBuilder->getQuery();

        $pagination = $this->getPaginator()->paginate(
            $query,
            $page,
            $limit
        );

        return $pagination;
    }

    /**
     * Get quote
     *
     * @param integer $commonId
     *
     * @return mixed Common or null
     *
     * @api 0.1
     */
    public function getQuote($commonId) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c, q')
            ->from('TeclliureInvoiceBundle:Common','c')
            ->innerJoin('c.quote','q')
            ->where('c.id = :commonId')
            ->setParameter('commonId', $commonId);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Create quote
     *
     * @return Common
     *
     * @api 0.1
     */
    public function createQuote() {
        $common = new Common();

        $quote = new Quote();
        if ($this->getConfig()->get('default_country')) {
            $common->setCustomerCountry($this->getConfig()->get('default_country'));
        }
        if ($this->getConfig()->get('default_footnote_quote')) {
            $quote->setFootnote($this->getConfig()->get('default_footnote_quote'));
        }
        $common->setQuote($quote);

        return $common;
    }

    /**
     * Save quote
     *
     * Save quote and calculate amounts
     *
     * @param Common $common Quote to save
     *
     * @api 0.1
     */
    public function saveQuote(Common $common, $originalLines = array()) {
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

        if (!$common->getQuote()) {
            throw new Exception('Common is not an quote');
        }
        elseif ($common->getQuote()->getStatus() > 0) {
            throw new Exception('Only quotes with status draft could be edited');
        }

        if (!$common->getQuote()->getNumber()) {
            // We get WRITE lock to avoid duplicated quote numbers
            $em = $this->getEntityManager();
            $em->getConnection()->exec('LOCK TABLE quote q0_ WRITE;');
            if (!$common->getQuote()->getNumber()) {
                if ($common->getQuote()->getCreated()) {
                    $createdDate = $common->getQuote()->getCreated();
                }
                else {
                    $createdDate = new \DateTime();
                }
                $nextQuoteNumber = $this->getNextQuoteNumber($createdDate);
                $common->getQuote()->setNumber($nextQuoteNumber);
            }
            $em->persist($common);
            $em->flush();
            $em->getConnection()->exec('UNLOCK TABLES;');
        }
        $this->updateCustomerFromCommon($common);

        $em = $this->getEntityManager();
        // print count($common->getCommonLines());
        $em->persist($common);
        $em->flush();
    }

    /**
     * Close quote
     *
     * Set status to closed and generate quote number
     *
     * @param Common $common Quote to close
     *
     * @api 0.1
     */
    public function closeQuote(Common $common) {
        if ($common->getQuote()->getStatus() != 0) {
            throw new Exception('Only quotes with status draft could be closed');
        }
        $common->getQuote()->setStatus(1);
        $em = $this->getEntityManager();
        $em->persist($common);
        $em->flush();

        // Dispatch Event
        $closeEvent = new CommonEvent($common);
        $closeEvent = $this->getEventDispatcher()->dispatch(CommonEvents::QUOTE_CLOSED, $closeEvent);

        if ($closeEvent->isPropagationStopped()) {
            // Things to do if stopped
        } else {
            // Things to do if not stopped
        }
    }

    /**
     * Deny quote
     *
     * Set status to closed and generate quote number
     *
     * @param Common $common Quote to close
     *
     * @api 0.1
     */
    public function denyQuote(Common $common) {
        if ($common->getQuote()->getStatus() > 1 ) {
            throw new Exception('Only quotes with status draft could be rejected');
        }
        $common->getQuote()->setStatus(2);
        $em = $this->getEntityManager();
        $em->persist($common);
        $em->flush();
    }

    /**
     * Open quote
     *
     * Set status to open
     *
     * @param Common $common Quote to open
     *
     * @api 0.1
     */
    public function openQuote(Common $common) {
        if (!($common->getQuote()->getStatus() > 0)) {
            throw new Exception('Only quotes with status different than draft could be opened');
        }
        $common->getQuote()->setStatus(0);
        $em = $this->getEntityManager();
        $em->persist($common);
        $em->flush();
    }

    /**
     * Get next quote number
     *
     * Get next quote number of a serie
     *
     * @param string Next quote number
     *
     * @api 0.1
     */
    protected function getNextQuoteNumber(\DateTime $date) {
        $queryParams = array();

        // We have the year at first
        $size = 6;
        $selectSubstring = 'MAX(SUBSTRING(q.number, '.$size.')) as number';

        // Filter by date
        $queryParams['startDate'] = new \DateTime('@'.mktime (0, 0, 0, 1, 1, $date->format('Y')));
        $queryParams['endDate'] = new \DateTime('@'.mktime (0, 0, 0, 12, 32, $date->format('Y')));


        $query = $this->em->createQuery('SELECT '.$selectSubstring.' FROM TeclliureInvoiceBundle:Quote q
        WHERE q.created >= :startDate AND q.created < :endDate ORDER BY q.number desc');
        $query->setParameters($queryParams);
        $result = $query->getOneOrNullResult();
        // print_r ($result);
        if (!$result || !$result['number']) {
            $number = 1;
        }
        else {
            $number = (int)$result['number']+1;
        }
        $number = 'Q'.$date->format('Y').str_pad($number, 8, '0', STR_PAD_LEFT);

        return $number;
    }
}
