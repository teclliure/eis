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
use Doctrine\ORM\EntityManager;
use Craue\ConfigBundle\Util\Config;
use Teclliure\InvoiceBundle\Entity\Payment;

/**
 * Payment service. It "should" be the ONLY class used directly by controllers.
 *
 * @author Marc Montañés Abarca <marc@teclliure.net>
 *
 * @api
 */
class PaymentService {
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
     * Get payments
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
    public function searchPayments($filters = array()) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
                        ->select('p, i')
                        ->from('TeclliureInvoiceBundle:Payment','p')
                        ->innerJoin('p.invoice','i');

        if ($filters) {
            if (isset($filters['invoice']) && $filters['invoice']) {
                $queryBuilder->where('i.id = :invoice_id')
                    ->setParameters(array(
                        'invoice_id'   => $filters['invoice']
                ));
                unset ($filters['invoice']);
            }

            if (isset($filters['start_date']) && $filters['start_date']) {
                $queryBuilder->andWhere('p.payment_date >= :start_date')
                    ->setParameter('start_date', $filters['start_date'],  \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['start_date']);
            }
            if (isset($filters['end_date']) && $filters['end_date']) {
                $queryBuilder->andWhere('p.payment_date <= :end_date')
                    ->setParameter('end_date', $filters['end_date'], \Doctrine\DBAL\Types\Type::DATETIME);
                unset ($filters['end_date']);
            }

            foreach ($filters as $key=>$filter) {
                if ($filter) {
                    $fieldName = preg_replace('/^i_/', 'i.',preg_replace('/^p_/', 'p.', $key));
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

        return $query->getResult();
    }

    /**
     * Get payment
     *
     * @param integer $id
     *
     * @return mixed Payment or null
     *
     * @api 0.1
     */
    public function getPayment($id, $new = false) {
        $queryBuilder = $this->getEntityManager()->createQueryBuilder()
            ->select('p, i')
            ->from('TeclliureInvoiceBundle:Payment','p')
            ->leftJoin('p.invoice','i')
            ->where('p.id = :id')
            ->setParameter('id', $id);

        return $queryBuilder->getQuery()->getOneOrNullResult();
    }

    /**
     * Create payment
     *
     * @return Payment
     *
     * @api 0.1
     */
    public function createPayment() {
        $payment = new Payment();
        $this->putDefaults($payment);
        return $payment;
    }

    public function putDefaults(Payment $payment) {
        if (!$payment->getPaymentDate()) {
            $date = new \DateTime('now');
            $payment->setPaymentDate($date);
        }
    }


    /**
     * Save payment
     *
     * Save payment and calculate due
     *
     * @param Payment $payment Invoice to save
     *
     * @api 0.1
     */
    public function savePayment(Payment $payment) {
        if (!$payment->getInvoice()) {
            throw new Exception('Payment should have an associated invoice');
        }
        elseif ($payment->getInvoice()->getStatus() == 0 || $payment->getInvoice()->getStatus() == 3) {
            throw new Exception('Only closed or overdued invoices could be paid. Current invoice status is:'.' '.$payment->getInvoice()->getStatusName());
        }
        else {
            $dueAmount = round($payment->getInvoice()->getGrossAmount()-$payment->getInvoice()->getPaidAmount()-$payment->getAmount(),2);
            if ($dueAmount < 0) {
                throw new Exception('Over payment not allowed. Max payment allowed '.$payment->getInvoice()->getDueAmount());
            }
            $payment->getInvoice()->setDueAmount($dueAmount);
        }

        if ($payment->getInvoice()->getDueAmount() == 0) {
            $payment->getInvoice()->setStatus(3);
        }
        elseif ($payment->getInvoice()->getDueAmount() && $payment->getInvoice()->getDueDate()->getTimestamp() < time()) {
            $payment->getInvoice()->setStatus(2);
        }

        $em = $this->getEntityManager();
        $em->persist($payment);
        $em->flush();
    }

    /**
     * Delete payment
     *
     * Delete payment and calculate due
     *
     * @param Payment $payment Invoice to save
     *
     * @api 0.1
     */
    public function deletePayment(Payment $payment) {
        $invoice = $payment->getInvoice();
        if (!$invoice) {
            throw new Exception('Payment should have an associated invoice');
        }
        else {
            $dueAmount = round($invoice->getGrossAmount()-$invoice->getPaidAmount()+$payment->getAmount(),2);
            $invoice->setDueAmount($dueAmount);
        }

        if ($invoice->getDueAmount() && $invoice->getDueDate()->getTimestamp() > time()) {
            $invoice->setStatus(2);
        }
        elseif ($invoice->getDueAmount() > 0 && $invoice->getStatus() == 3) {
            $invoice->setStatus(1);
        }

        $em = $this->getEntityManager();
        $em->persist($invoice);
        $em->remove($payment);
        $em->flush();
    }
}