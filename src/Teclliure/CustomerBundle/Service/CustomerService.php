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
use Craue\ConfigBundle\Util\Config;
use Knp\Component\Pager\Paginator;
use Teclliure\CustomerBundle\Entity\Customer;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;

/**
 * Customer service. It "should" be the ONLY class used directly by controllers in order to deal with customers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class CustomerService implements PaginatorAwareInterface {
    /**
     * Entity Manager
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
    public function searchCustomers($search, $limit = 10, $offset = 0, $andOr = 'AND') {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('c')
                        ->from('TeclliureCustomerBundle:Customer','c');

        if ($search) {
            foreach ($search as $key => $find) {
                if ($andOr == 'AND') {
                    $queryBuilder->andWhere('c.'.$key. ' LIKE :find');
                }
                else {
                    $queryBuilder->orWhere('c.'.$key. ' LIKE :find');
                }
                $queryBuilder->setParameter('find', '%'.$find.'%');
            }
        }
        // $queryBuilder->setParameter('where', $where);
        $queryBuilder->setMaxResults($limit);
        $queryBuilder->setFirstResult($offset);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get customers
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
    public function getCustomers($limit = 10, $page = 1, $filters = array()) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from('TeclliureCustomerBundle:Customer','c');

        if ($filters) {
            if (isset($filters['search']) && $filters['search']) {
                $queryBuilder->where('c.name LIKE :search')
                    ->setParameters(array(
                        'search'    => '%'.$filters['search'].'%'
                    ));
                unset ($filters['search']);
            }

            foreach ($filters as $key=>$filter) {
                if ($filter) {
                    $fieldName = preg_replace('/^c_/', 'c.', $key);
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
     * Get customer
     *
     * @param integer $id
     *
     * @return mixed Customer or null
     *
     * @api 0.1
     */
    public function getCustomer($id) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c, i')
            ->from('TeclliureCustomerBundle:Customer','c')
            ->where('c.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Create customer
     *
     * @return Customer
     *
     * @api 0.1
     */
    public function createCustomer() {
        $customer = new Customer();
        $this->putDefaults($customer);
        return $customer;
    }

    public function putDefaults(Customer $customer) {
        if (!$customer->getCountry()) {
            if ($this->getConfig()->get('default_country')) {
                $customer->setCountry($this->getConfig()->get('default_country'));
            }
        }
    }

    /**
     * Save customer
     *
     * Save customer
     *
     * @param Customer $customer Customer to save
     *
     * @api 0.1
     */
    public function saveCustomer(Customer $customer) {
        $em = $this->getEntityManager();
        $em->persist($customer);
        $em->flush();
    }
}