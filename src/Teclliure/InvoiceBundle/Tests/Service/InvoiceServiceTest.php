<?php

namespace Teclliure\InvoiceBundle\Tests\Service;

use Teclliure\InvoiceBundle\Service\InvoiceService;


class InvoiceServiceTest extends \PHPUnit_Framework_TestCase
{
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

  /*protected function getEntityRepositoryMock() {
    $erMock = $this->getMockBuilder('\Doctrine\ORM\EntityRepository')
        ->disableOriginalConstructor()
        ->getMock();
    return $erMock;
  }*/

  protected function getQueryMock() {
    $queryMock =  $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
        ->disableOriginalConstructor()
        ->setMethods(array('getResult', 'getSQL', '_doExecute', 'setParameter','createQuery'))
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
}