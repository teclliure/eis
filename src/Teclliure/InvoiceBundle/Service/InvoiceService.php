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

use Doctrine\ORM\EntityManager;


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
}