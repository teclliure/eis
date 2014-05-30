<?php

namespace Teclliure\InvoiceBundle\Tests\Service;

use Teclliure\InvoiceBundle\Entity\DeliveryNote;
use Teclliure\InvoiceBundle\Service\InvoiceService;
use Teclliure\InvoiceBundle\Entity\Invoice;
use Teclliure\InvoiceBundle\Entity\Common;

class InvoiceServiceTest extends \PHPUnit_Framework_TestCase
{

    protected $emMock;
    protected $configMock;
    protected $eventDispatcherMock;
    protected $customCheckerMock;

    protected function getEntityManagerMock() {
        $emMock = $this->getMockBuilder('\Doctrine\ORM\EntityManager')
        ->disableOriginalConstructor()
        ->getMock();
        return $emMock;
    }

    protected function getQueryBuilderMock() {
        $queryBuilderMock = $this->getMockBuilder('\Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        return $queryBuilderMock;
    }

    protected function getConfigMock() {
        $configMock = $this->getMockBuilder('\Craue\ConfigBundle\Util\Config')
            ->disableOriginalConstructor()
            ->getMock();
        return $configMock;
    }

    protected function getEventDispatcherMock() {
        $eventDispatcherMock = $this->getMockBuilder('\Symfony\Component\EventDispatcher\EventDispatcher')
            ->getMock();
        return $eventDispatcherMock;
    }

    protected function getCustomCheckerMock() {
        $customCheckerMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Util\DoctrineCustomChecker')
            ->disableOriginalConstructor()
            ->getMock();
        return $customCheckerMock;
    }

    public function getPaginatoMock() {
        $paginatorMock = $this->getMockBuilder('\Knp\Component\Pager\Paginator')
            ->disableOriginalConstructor()
            ->getMock();
        return $paginatorMock;
    }

    protected function getEntityRepositoryMock() {
        $erMock = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->getMock();
        return $erMock;
    }

    protected function getQueryMock() {
        $queryMock =  $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult', 'getSQL', '_doExecute', 'setParameter','createQuery','getOneOrNullResult'))
            ->getMock();
        return $queryMock;
    }

    protected function getCustomerMock() {
        $customerMock =  $this->getMockBuilder('\Teclliure\InvoiceBundle\Model\CustomerInterface')
            ->disableOriginalConstructor()
            ->setMethods(array('getId'))
            ->getMock();
        return $customerMock;
    }

    protected function getInvoiceService() {
        $this->emMock = $this->getEntityManagerMock();
        $this->configMock = $this->getConfigMock();
        $this->eventDispatcherMock = $this->getEventDispatcherMock();
        $this->customCheckerMock = $this->getCustomCheckerMock();

        return new InvoiceService($this->emMock, $this->configMock, $this->eventDispatcherMock, $this->customCheckerMock);
    }

    protected function common_getInvoices($queryBuilderMock, $queryMock) {
        $this->emMock->expects($this->once())
          ->method('createQueryBuilder')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('select')
          ->with('i, c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('from')
          ->with('TeclliureInvoiceBundle:Invoice','i')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('innerJoin')
          ->with('i.common','c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('getQuery')
          ->will($this->returnValue($queryMock));
    }

    public function testGetInvoices_noFiltersNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $expected = array();

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatoMock();

        $invoiceService = $this->getInvoiceService();
        $invoiceService->setPaginator($paginatorMock);

        $this->common_getInvoices($queryBuilderMock, $queryMock);

        $paginatorMock->expects($this->once())
          ->method('paginate')
          ->with($queryMock, $page, $limit)
          ->will($this->returnValue($expected));
        ;

        $queryBuilderMock->expects($this->never())
            ->method('where');

        $actual = $invoiceService->getInvoices($limit, $page, array(), null);
        $this->assertEquals($expected, $actual, 'Error getting invoices' );
    }

    public function testGetInvoices_noFiltersNoSortWithLimitNoPage_methodCalls()
    {
        $page = null;
        $limit = 15;
        $expected = array();

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatoMock();

        $invoiceService = $this->getInvoiceService();
        $invoiceService->setPaginator($paginatorMock);

        $this->common_getInvoices($queryBuilderMock, $queryMock);

        $queryMock->expects($this->once())
          ->method('getResult')
          ->will($this->returnValue($expected));

        $actual = $invoiceService->getInvoices($limit, $page, array(), null);
        $this->assertEquals($expected, $actual, 'Error getting invoices' );
    }

    public function testGetInvoices_filtersNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $filters = array('search'=>'testSearch', 'i_number'=>23213);

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatoMock();

        $invoiceService = $this->getInvoiceService();
        $invoiceService->setPaginator($paginatorMock);

        $this->common_getInvoices($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('where')
          ->with('i.number LIKE :search OR c.customer_name LIKE :search2')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameters')
          ->with(array(
            'search'    => '%'.$filters['search'].'%',
            'search2'   => '%'.$filters['search'].'%'
          ))
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('i.number'.' LIKE :where'.'i_number')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'i_number', '%'.'23213'.'%')
          ->will($this->returnValue($queryBuilderMock));

        $invoiceService->getInvoices($limit, $page, $filters, null);
    }

    public function testGetInvoices_filtersObjectNoSortWithLimitWithPage_methodCalls()
    {
        $customerMock = $this->getCustomerMock();
        $page = 2;
        $limit = 15;
        $filters = array('i_customer'=>$customerMock);

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatoMock();

        $invoiceService = $this->getInvoiceService();
        $invoiceService->setPaginator($paginatorMock);

        $this->common_getInvoices($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('i.customer'.' = :where'.'i_customer')
          ->will($this->returnValue($queryBuilderMock));

        $customerMock->expects($this->once())
          ->method('getId')
          ->will($this->returnValue(111));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'i_customer', '111')
          ->will($this->returnValue($queryBuilderMock));

        $invoiceService->getInvoices($limit, $page, $filters, null);
    }

    public function testGetInvoices_filtersArrayNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $filters = array('i_customer'=>array(1, 3, 2, 44));

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatoMock();

        $invoiceService = $this->getInvoiceService();
        $invoiceService->setPaginator($paginatorMock);

        $this->common_getInvoices($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('i.customer'.' IN (:wherei_customer)')
          ->will($this->returnValue($queryBuilderMock));


        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'i_customer', array(1, 3, 2, 44))
          ->will($this->returnValue($queryBuilderMock));

        $invoiceService->getInvoices($limit, $page, $filters, null);
    }

    public function testGetInvoices_noFiltersSortWithLimitNoPage_methodCalls()
    {
        $page = null;
        $limit = 15;
        $sort = array(array('sort'=>'i.customer', 'sortOrder'=>'DESC'));

        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryMock = $this->getQueryMock();
        $invoiceService = $this->getInvoiceService();

        $this->common_getInvoices($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->at(3))
          ->method('addOrderBy')
          ->with('i.customer', 'DESC')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->at(4))
          ->method('addOrderBy')
          ->with('i.issue_date', 'DESC')
          ->will($this->returnValue($queryBuilderMock));

        $invoiceService->getInvoices($limit, $page, array(), $sort);
    }

    public function testGetInvoice_methodCalls()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryMock = $this->getQueryMock();
        $invoiceService = $this->getInvoiceService();

        $invoice = new Invoice();

        $this->emMock->expects($this->once())
          ->method('createQueryBuilder')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('select')
          ->with('c, i')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('from')
          ->with('TeclliureInvoiceBundle:Invoice','i')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('where')
          ->with('i.id = :invoiceId')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('invoiceId', $invoice->getId())
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('innerJoin')
          ->with('i.common','c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('getQuery')
          ->with()
          ->will($this->returnValue($queryMock));

        $queryMock->expects($this->once())
          ->method('getOneOrNullResult')
          ->with()
          ->will($this->returnValue($invoice));

        $actual = $invoiceService->getInvoice($invoice->getId());
        $this->assertEquals($invoice, $actual, 'Error getting invoice' );
    }

    public function testGetInvoicesView_methodCalls()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryMock = $this->getQueryMock();

        $entityRepositoryMock = $this->getEntityRepositoryMock();
        $invoiceService = $this->getInvoiceService();
        $expected = array('dsadsa', '43erewre');

        $this->emMock->expects($this->once())
          ->method('getRepository')
          ->with('TeclliureInvoiceBundle:DeliveryNote')
          ->will($this->returnValue($entityRepositoryMock));

        $entityRepositoryMock->expects($this->once())
          ->method('find')
          ->with(4)
          ->will($this->returnValue(new DeliveryNote()));

        $this->common_getInvoices($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('i.related_delivery_note'.' = :where'.'i_related_delivery_note')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'i_related_delivery_note', null)
          ->will($this->returnValue($queryBuilderMock));

        $queryMock->expects($this->once())
          ->method('getResult')
          ->will($this->returnValue($expected));

        $actual = $invoiceService->getInvoicesView(10, null, 4, 'deliveryNote');
        $this->assertEquals($expected, $actual, 'Error getting invoices view');
    }

    public function testCreateInvoice_methodCalls()
    {
        $invoiceService = $this->getInvoiceService();
        $expected = new Invoice();
        $invoiceService->putDefaults($expected);

        $actual = $invoiceService->createInvoice();
        $this->assertEquals($expected, $actual, 'Error creating invoice');
    }

    public function testPutDefaults_methodCalls()
    {
        $invoiceService = $this->getInvoiceService();
        $invoiceMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Invoice')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();
        $serieMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Serie')->getMock();

        $invoiceMock->expects($this->exactly(2))
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $invoiceMock->expects($this->once())
            ->method('getIssueDate')
            ->with()
            ->will($this->returnValue(null));

        $invoiceMock->expects($this->once())
            ->method('setIssueDate')
            ->with(new \DateTime('now'))
            ->will($this->returnValue(null));

        $invoiceMock->expects($this->once())
            ->method('getDueDate')
            ->with()
            ->will($this->returnValue(null));

        $dueDate = new \DateTime('now');
        $invoiceMock->expects($this->once())
            ->method('setDueDate')
            ->with($dueDate->modify('+1 month'))
            ->will($this->returnValue(null));

        $invoiceMock->expects($this->once())
            ->method('getSerie')
            ->with()
            ->will($this->returnValue(null));

        $this->configMock->expects($this->at(0))
            ->method('get')
            ->with('default_serie')
            ->will($this->returnValue(4));

        $entityRepositoryMock = $this->getEntityRepositoryMock();
        $this->emMock->expects($this->once())
            ->method('getRepository')
            ->with('TeclliureInvoiceBundle:Serie')
            ->will($this->returnValue($entityRepositoryMock));

        $this->configMock->expects($this->at(1))
            ->method('get')
            ->with('default_serie')
            ->will($this->returnValue(4));

        $entityRepositoryMock->expects($this->once())
            ->method('find')
            ->with(4)
            ->will($this->returnValue($serieMock));

        $invoiceMock->expects($this->once())
            ->method('setSerie')
            ->with($serieMock)
            ->will($this->returnValue(null));

        $commonMock->expects($this->once())
            ->method('getCustomerCountry')
            ->with()
            ->will($this->returnValue(null));

        $this->configMock->expects($this->at(2))
            ->method('get')
            ->with('default_country')
            ->will($this->returnValue('Country'));

        $commonMock->expects($this->once())
            ->method('setCustomerCountry')
            ->with('Country dsadsds')
            ->will($this->returnValue(null));

        $this->configMock->expects($this->at(3))
            ->method('get')
            ->with('default_country')
            ->will($this->returnValue('Country dsadsds'));

        $this->configMock->expects($this->at(4))
            ->method('get')
            ->with('default_footnote_invoice')
            ->will($this->returnValue('Test footnote'));

        $invoiceMock->expects($this->once())
            ->method('getFootnote')
            ->with()
            ->will($this->returnValue(null));

        $invoiceMock->expects($this->once())
            ->method('setFootnote')
            ->with('Test footnote')
            ->will($this->returnValue(null));

        $this->configMock->expects($this->at(5))
            ->method('get')
            ->with('default_footnote_invoice')
            ->will($this->returnValue('Test footnote'));

        $invoiceService->putDefaults($invoiceMock);
    }

    public function testPutDefaults_correctOutput()
    {
        $invoiceService = $this->getInvoiceService();

        $expected = new Invoice();
        $expected->setIssueDate(new \DateTime('now'));
        $dueDate = new \DateTime('now');
        $expected->setDueDate($dueDate->modify('+1 month'));
        $expected->setCommon(new Common());

        $actual = new Invoice();
        $invoiceService->putDefaults($actual);

        $this->assertEquals((array)$expected, (array)$actual, 'Error putting defaults to invoice');
    }

}