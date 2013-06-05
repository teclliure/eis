<?php
/**
 * This file is part of Teclliure developed package build on 2013.
 *
 * (c) Marc Montañés Abarca <marc@teclliure.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Teclliure\CustomerBundle\Service;

use Doctrine\ORM\EntityManager;

/**
 * Customer service. It "should" be the ONLY class used directly by controllers in order to deal with customers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class CustomerService {

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
     * Search customers
     *
     * @param array $search fields
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     *
     * @api 0.1
     */
    public function searchCustomers($search, $limit = 10, $offset = 0) {
        //$query = $this->getEntityManager()->createQueryBuilder('SELECT c,i FROM TeclliureInvoiceBundle:Common c LEFT JOIN c.invoice i :where ORDER BY :order');
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('c')
                        ->from('TeclliureCustomerBundle:Customer','c');

        if ($search) {
            foreach ($search as $key => $find) {
                $queryBuilder->andWhere('c.'.$key. ' LIKE :find');
                $queryBuilder->setParameter('find', '%'.$find.'%');
            }
        }
        // $queryBuilder->setParameter('where', $where);
        $queryBuilder->setMaxResults($limit);
        $queryBuilder->setFirstResult($offset);

        return $queryBuilder->getQuery()->getResult();
    }

//    /**
//     * Get invoice
//     *
//     * @param integer $commonId
//     *
//     * @return mixed Common or null
//     *
//     * @api 0.1
//     */
//    public function getInvoice($commonId) {
//        // $query = $this->getEntityManager()->createQueryBuilder('SELECT c,i FROM TeclliureInvoiceBundle:Common c LEFT JOIN c.invoice i :where ORDER BY :order');
//        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
//            ->select('c, i')
//            ->from('TeclliureInvoiceBundle:Common','c')
//            ->innerJoin('c.invoice','i')
//            ->where('c.id = :commonId')
//            ->setParameter('commonId', $commonId);
//
//        return $queryBuilder->getQuery()->getOneOrNullResult();
//    }
//
//    /**
//     * Create invoice
//     *
//     * @return Common
//     *
//     * @api 0.1
//     */
//    public function createInvoice() {
//        $invoice = new Common();
//
////        return $invoice;
////    }
}