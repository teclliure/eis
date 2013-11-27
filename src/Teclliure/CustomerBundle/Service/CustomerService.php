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
use Teclliure\CustomerBundle\Entity\Contact;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Teclliure\InvoiceBundle\Util\DoctrineCustomChecker;

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
     * @var DoctrineCustomChecker
     */
    private $customChecker;

    /**
     * Constructor.
     *
     * @param EntityManager
     *
     */
    public function __construct(EntityManager $em, Config $config, DoctrineCustomChecker $customChecker) {
        $this->em = $em;
        $this->config = $config;
        $this->customChecker = $customChecker;
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

                if ($key == 'name') {
                    $dql = 'c.name LIKE :find OR c.legal_name LIKE :find2';
                }
                else {
                    // Check that key is a a field of the table to avoid SQL Injection
                    $this->customChecker->checkTableFieldExists('TeclliureCustomerBundle:Customer', $key);
                    $dql = 'c.'.$key. ' LIKE :find';
                }
                if ($andOr == 'AND') {
                    $queryBuilder->andWhere($dql);
                }
                else {
                    $queryBuilder->orWhere($dql);
                }
                if ($key == 'name') {
                    $queryBuilder->setParameters(array('find' => '%'.$find.'%', 'find2' => '%'.$find.'%'));
                }
                else {
                    $queryBuilder->setParameter('find', '%'.$find.'%');
                }
            }
        }
        $queryBuilder->addOrderBy('c.legal_name', 'ASC');
        // $queryBuilder->setParameter('where', $where);
        $queryBuilder->setMaxResults($limit);
        $queryBuilder->setFirstResult($offset);

        $results = $queryBuilder->getQuery()->getResult();

        return $results;
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
            if (array_key_exists('search', $filters)) {
                if ($filters['search']) {
                    $queryBuilder->where('c.name LIKE :search OR c.legal_name LIKE :search2')
                        ->setParameters(array(
                            'search'    => '%'.$filters['search'].'%',
                            'search2'    => '%'.$filters['search'].'%'
                        ));
                }
                unset ($filters['search']);
            }


            foreach ($filters as $key=>$filter) {
                // print $key.'-'.$filter.'__';
                if ($filter !== '') {
                    $fieldName = preg_replace('/^c_/', 'c.', $key);
                    $value = $filter;
                    $this->customChecker->checkTableFieldExists('TeclliureCustomerBundle:Customer', $key);
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
        $queryBuilder->addOrderBy('c.legal_name', 'ASC');
        $query = $queryBuilder->getQuery();

        $pagination = $this->getPaginator()->paginate(
            $query,
            $page,
            $limit
        );
        $this->addTotals($pagination);

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
            ->select('c')
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

    /**
     * Disable customer
     *
     * @var Customer $customer
     *
     * @api 0.1
     */
    public function disableCustomer(Customer $customer) {
        if ($customer->getActive() != 1) {
            throw new \Exception('Only active customers could be disabled');
        }
        $customer->setActive(0);
        $em = $this->getEntityManager();
        $em->persist($customer);
        $em->flush();
    }

    /**
     * Enable customer
     *
     * @var Customer $customer
     *
     * @api 0.1
     */
    public function enableCustomer(Customer $customer) {
        if ($customer->getActive() != 0) {
            throw new \Exception('Only disabled customers could be enabled');
        }
        $customer->setActive(1);
        $em = $this->getEntityManager();
        $em->persist($customer);
        $em->flush();
    }

    /**
     * Delete customer
     *
     * @var Customer $customer
     *
     * @api 0.1
     */
    public function deleteCustomer(Customer $customer) {
        $em = $this->getEntityManager();
        $em->remove($customer);
        $em->flush();
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
    public function saveCustomer(Customer $customer, $originalContacts = array()) {
        if ($originalContacts)  {
            foreach ($customer->getContacts() as $contact) {
                foreach ($originalContacts as $key => $toDel) {
                    if ($toDel->getId() === $contact->getId()) {
                        unset($originalContacts[$key]);
                    }
                }
            }

            // remove the relationship between the line and the common
            foreach ($originalContacts as $contact) {
                $this->getEntityManager()->remove($contact);
            }
        }

        $em = $this->getEntityManager();
        $em->persist($customer);
        $em->flush();
    }

    /**
     * Get customer total payments amount
     *
     * @param Customer $customer Customer
     * @return float Amount
     *
     * @api 0.1
     */
    protected function getCustomerPayments(Customer $customer) {
        $total = 0;
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('i, c, p')
            ->from('TeclliureInvoiceBundle:Invoice','i')
            ->innerJoin('i.common','c')
            ->leftJoin('i.payments','p')
            ->where('c.customer = :customer')
            ->setParameter('customer', $customer->getId());

        $results = $queryBuilder->getQuery()->getResult();

        foreach ($results as $result) {
            foreach ($result->getPayments() as $payment) {
                $total += $payment->getAmount();
            }
        }
        return $total;
    }

    /**
     * Get customer total payments amount
     *
     * @param Customer $customer Customer
     * @param string $type Document type: invoice, quote, ...
     *
     * @return Contact
     *
     * @api 0.1
     */
    public function getContact(Customer $customer, $type) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from('TeclliureCustomerBundle:Contact','c')
            ->innerJoin('c.customer', 'customer')
            ->where('customer.id = :customer_id')
            ->andWhere('c.send_'.$type.' = 1')
            ->setParameter('customer_id', $customer->getId())
            ->setMaxResults(1);

       $contact = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!$contact) {
            $contact = new Contact();
        }

        return $contact;
    }

    /**
     * Get customer total payments amount
     *
     * @param Customer $customer Customer
     * @param string $type Document type: invoice, quote, ...
     *
     * @return Contact
     *
     * @api 0.1
     */
    public function getContacts($customerId, $search) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('c')
            ->from('TeclliureCustomerBundle:Contact','c')
            ->innerJoin('c.customer', 'customer')
            ->where('customer.id = :customer_id')
            ->andWhere('c.name LIKE :search OR c.email LIKE :search')
            ->setParameter('customer_id', $customerId)
            ->setParameter('search', '%'.$search.'%');

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Get customer total due
     *
     * @param Customer $customer Customer
     * @return float Amount
     *
     * @api 0.1
     */
    protected function getCustomerDue(Customer $customer) {
        $total = 0;
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('i, c')
            ->from('TeclliureInvoiceBundle:Invoice','i')
            ->innerJoin('i.common','c')
            ->where('c.customer = :customer')
            ->setParameter('customer', $customer->getId());

        $results = $queryBuilder->getQuery()->getResult();

        foreach ($results as $result) {
            $total += $result->getDueAmount();
        }
        return $total;
    }


    protected function addTotals ($results) {
        foreach ($results as $key=>$result) {
            $result->setTotalPaid($this->getCustomerPayments($result));
            $result->setTotalDue($this->getCustomerDue($result));
        }
    }

    public function calculateDueDate($customerId = null, $issueDate) {
        $daysToPay = 30;
        if ($customerId) {
            $customer = $this->getCustomer($customerId);
            if ($customer->getPaymentPeriod()) {
                $daysToPay = $customer->getPaymentPeriod();
            }
        }
        //print $daysToPay.'__'.$daysToPay%30;
        if (!$daysToPay%30) {
            $months = $daysToPay/30;
            if ($months > 1) {
                $issueDate->modify('+'.$months.' months');
            }
            else {
                $issueDate->modify('+'.$months.' month');
            }
        }
        else {
            $issueDate->modify('+'.$daysToPay.' days');
        }
        return $issueDate;
    }
}
