<?php

namespace Teclliure\InvoiceBundle\Tests\Service;

use Teclliure\InvoiceBundle\Service\DeliveryNoteService;
use Teclliure\InvoiceBundle\Entity\DeliveryNote;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\InvoiceBundle\Entity\Invoice;
use Teclliure\InvoiceBundle\CommonEvents;
use Teclliure\InvoiceBundle\Event\CommonEvent;
use Teclliure\InvoiceBundle\Event\DeliveryNoteEvent;
use Teclliure\InvoiceBundle\Event\InvoiceEvent;
use Symfony\Component\Security\Acl\Exception\Exception;

class DeliveryNoteServiceTest extends \PHPUnit_Framework_TestCase
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

    public function getPaginatorMock() {
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
            ->setMethods(array('getResult', 'getSQL', '_doExecute', 'setParameter','createQuery','getOneOrNullResult', 'setParameters'))
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

    protected function getDeliveryNoteService() {
        $this->emMock = $this->getEntityManagerMock();
        $this->configMock = $this->getConfigMock();
        $this->eventDispatcherMock = $this->getEventDispatcherMock();
        $this->customCheckerMock = $this->getCustomCheckerMock();

        return new DeliveryNoteService($this->emMock, $this->configMock, $this->eventDispatcherMock, $this->customCheckerMock);
    }

    protected function common_getDeliveryNotes($queryBuilderMock, $queryMock) {
        $this->emMock->expects($this->once())
          ->method('createQueryBuilder')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('select')
          ->with('d, c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('from')
          ->with('TeclliureInvoiceBundle:DeliveryNote','d')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('innerJoin')
          ->with('d.common','c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('getQuery')
          ->will($this->returnValue($queryMock));
    }

    public function testGetDeliveryNotes_noFiltersNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $expected = array();

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $deliveryNoteService = $this->getDeliveryNoteService();
        $deliveryNoteService->setPaginator($paginatorMock);

        $this->common_getDeliveryNotes($queryBuilderMock, $queryMock);

        $paginatorMock->expects($this->once())
          ->method('paginate')
          ->with($queryMock, $page, $limit)
          ->will($this->returnValue($expected));
        ;

        $queryBuilderMock->expects($this->never())
            ->method('where');

        $actual = $deliveryNoteService->getDeliveryNotes($limit, $page, array(), null);
        $this->assertEquals($expected, $actual, 'Error getting deliveryNotes' );
    }

    public function testGetDeliveryNotes_noFiltersNoSortWithLimitNoPage_methodCalls()
    {
        $page = null;
        $limit = 15;
        $expected = array();

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $deliveryNoteService = $this->getDeliveryNoteService();
        $deliveryNoteService->setPaginator($paginatorMock);

        $this->common_getDeliveryNotes($queryBuilderMock, $queryMock);

        $queryMock->expects($this->once())
          ->method('getResult')
          ->will($this->returnValue($expected));

        $actual = $deliveryNoteService->getDeliveryNotes($limit, $page, array(), null);
        $this->assertEquals($expected, $actual, 'Error getting deliveryNotes' );
    }

    public function testGetDeliveryNotes_filtersNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $filters = array('search'=>'testSearch', 'd_number'=>23213);

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $deliveryNoteService = $this->getDeliveryNoteService();
        $deliveryNoteService->setPaginator($paginatorMock);

        $this->common_getDeliveryNotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('where')
          ->with('d.number LIKE :search OR c.customer_name LIKE :search2')
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
          ->with('d.number'.' LIKE :where'.'d_number')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'d_number', '%'.'23213'.'%')
          ->will($this->returnValue($queryBuilderMock));

        $deliveryNoteService->getDeliveryNotes($limit, $page, $filters, null);
    }

    public function testGetDeliveryNotes_filtersObjectNoSortWithLimitWithPage_methodCalls()
    {
        $customerMock = $this->getCustomerMock();
        $page = 2;
        $limit = 15;
        $filters = array('d_customer'=>$customerMock);

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $deliveryNoteService = $this->getDeliveryNoteService();
        $deliveryNoteService->setPaginator($paginatorMock);

        $this->common_getDeliveryNotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('d.customer'.' = :where'.'d_customer')
          ->will($this->returnValue($queryBuilderMock));

        $customerMock->expects($this->once())
          ->method('getId')
          ->will($this->returnValue(111));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'d_customer', '111')
          ->will($this->returnValue($queryBuilderMock));

        $deliveryNoteService->getDeliveryNotes($limit, $page, $filters, null);
    }

    public function testGetDeliveryNotes_filtersArrayNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $filters = array('d_customer'=>array(1, 3, 2, 44));

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $deliveryNoteService = $this->getDeliveryNoteService();
        $deliveryNoteService->setPaginator($paginatorMock);

        $this->common_getDeliveryNotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('d.customer'.' IN (:whered_customer)')
          ->will($this->returnValue($queryBuilderMock));


        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'d_customer', array(1, 3, 2, 44))
          ->will($this->returnValue($queryBuilderMock));

        $deliveryNoteService->getDeliveryNotes($limit, $page, $filters, null);
    }

    public function testGetDeliveryNotes_noFiltersSortWithLimitNoPage_methodCalls()
    {
        $page = null;
        $limit = 15;
        $sort = array(array('sort'=>'d.customer', 'sortOrder'=>'DESC'));

        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryMock = $this->getQueryMock();
        $deliveryNoteService = $this->getDeliveryNoteService();

        $this->common_getDeliveryNotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->at(3))
          ->method('addOrderBy')
          ->with('d.customer', 'DESC')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->at(4))
          ->method('addOrderBy')
          ->with('d.created', 'DESC')
          ->will($this->returnValue($queryBuilderMock));

        $deliveryNoteService->getDeliveryNotes($limit, $page, array(), $sort);
    }

    public function testGetDeliveryNote_methodCalls()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryMock = $this->getQueryMock();
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNote = new DeliveryNote();

        $this->emMock->expects($this->once())
          ->method('createQueryBuilder')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('select')
          ->with('c, d')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('from')
          ->with('TeclliureInvoiceBundle:DeliveryNote','d')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('where')
          ->with('d.id = :deliveryNoteId')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('deliveryNoteId', $deliveryNote->getId())
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('innerJoin')
          ->with('d.common','c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('getQuery')
          ->with()
          ->will($this->returnValue($queryMock));

        $queryMock->expects($this->once())
          ->method('getOneOrNullResult')
          ->with()
          ->will($this->returnValue($deliveryNote));

        $actual = $deliveryNoteService->getDeliveryNote($deliveryNote->getId());
        $this->assertEquals($deliveryNote, $actual, 'Error getting deliveryNote' );
    }

    public function testCreateDeliveryNote_methodCalls()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();
        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();

        $deliveryNoteMock->expects($this->atLeastOnce())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $this->configMock->expects($this->at(0))
            ->method('get')
            ->with('default_country')
            ->will($this->returnValue('Country dsadsds'));

        $commonMock->expects($this->once())
            ->method('setCustomerCountry')
            ->with('Country dsadsds')
            ->will($this->returnValue(null));

        $this->configMock->expects($this->at(1))
            ->method('get')
            ->with('default_country')
            ->will($this->returnValue('Country dsadsds'));

        $this->configMock->expects($this->at(2))
            ->method('get')
            ->with('default_footnote_order')
            ->will($this->returnValue('Footnote sdsd'));

        $deliveryNoteMock->expects($this->once())
            ->method('setFootnote')
            ->with('Footnote sdsd')
            ->will($this->returnValue(null));

        $this->configMock->expects($this->at(3))
            ->method('get')
            ->with('default_footnote_order')
            ->will($this->returnValue('Footnote sdsd'));
        // $deliveryNoteService->putDefaults($expected);

        $deliveryNoteService->createDeliveryNote($deliveryNoteMock);
    }

    public function testCreateDeliveryNote_correctOutput()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();
        $expected = new DeliveryNote();
        $common = new Common();
        $expected->setCommon($common);

        $actual = $deliveryNoteService->createDeliveryNote();
        $this->assertEquals((array)$expected, (array)$actual, 'Error creating deliveryNote');
    }

    /**
     *
     *
     */
    public function testSaveDeliveryNote_methodsCall_createNumber()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $deliveryNoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $deliveryNoteMock->expects($this->any())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(0));

        $deliveryNoteMock->expects($this->once())
            ->method('getNumber')
            ->with()
            ->will($this->returnValue(23));

        $deliveryNoteService->saveDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     *
     */
    public function testSaveDeliveryNote_methodsCall()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $deliveryNoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $deliveryNoteMock->expects($this->any())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(0));

        $deliveryNoteMock->expects($this->once())
            ->method('getNumber')
            ->with()
            ->will($this->returnValue('DeliveryNote Nb'));

        $deliveryNoteService->saveDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only orders with status draft could be edited
     *
     */
    public function testSaveDeliveryNote_methodsCallException()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $deliveryNoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));


        $deliveryNoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $deliveryNoteService->saveDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     *
     */
    public function testSaveDeliveryNote_EventsLaunch()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $queryMock = $this->getQueryMock();
        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();
        $connectionMock = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $deliveryNoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $deliveryNoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(null));

        $deliveryNoteMock->expects($this->atLeastOnce())
            ->method('getNumber')
            ->with()
            ->will($this->returnValue(null));

        $deliveryNoteMock->expects($this->once())
            ->method('setNumber')
            ->with()
            ->will($this->returnValue(4));

        $this->emMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->with()
            ->will($this->returnValue($connectionMock));

        $connectionMock->expects($this->at(0))
            ->method('exec')
            ->with('LOCK TABLE delivery_note d0_ WRITE;');

        $connectionMock->expects($this->at(1))
            ->method('exec')
            ->with('UNLOCK TABLES;');

        $this->emMock->expects($this->once())
            ->method('createQuery')
            ->with('SELECT MAX(SUBSTRING(d.number, 7)) as number FROM TeclliureInvoiceBundle:DeliveryNote d
        WHERE d.created >= :startDate AND d.created < :endDate ORDER BY d.number desc')
            ->will($this->returnValue($queryMock));

        $queryParams = array();
        $date = new \DateTime();
        $queryParams['startDate'] = new \DateTime('@'.mktime (0, 0, 0, 1, 1, $date->format('Y')));
        $queryParams['endDate'] = new \DateTime('@'.mktime (0, 0, 0, 12, 32, $date->format('Y')));
        $queryMock->expects($this->once())
            ->method('setParameters')
            ->with($queryParams)
            ->will($this->returnValue(null));

        /*$commonMock->expects($this->once())
            ->method('getCommonLines')
            ->with()
            ->will($this->returnValue(array($commonLine)));


        $commonLineMock->expects($this->once())
            ->method('getId')
            ->with()
            ->will($this->returnValue(array($commonLine)));*/


        $this->eventDispatcherMock->expects($this->at(0))
            ->method('dispatch')
            ->with(CommonEvents::DELIVERY_NOTE_PRE_SAVED, new DeliveryNoteEvent($deliveryNoteMock));

        $this->eventDispatcherMock->expects($this->at(1))
            ->method('dispatch')
            ->with(CommonEvents::DELIVERY_NOTE_SAVED, new DeliveryNoteEvent($deliveryNoteMock));

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($deliveryNoteMock);

        $this->emMock->expects($this->once())
            ->method('flush');

        $deliveryNoteService->saveDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     *
     */
    public function testCloseDeliveryNote_EventsLaunch()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $deliveryNoteMock->expects($this->once())
            ->method('setStatus')
            ->with(1);

        $deliveryNoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $this->eventDispatcherMock->expects($this->at(0))
            ->method('dispatch')
            ->with(CommonEvents::DELIVERY_NOTE_CLOSED, new CommonEvent($commonMock));

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($deliveryNoteMock);

        $this->emMock->expects($this->once())
            ->method('flush');

        $deliveryNoteService->closeDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only orders with status draft could be closed
     *
     */
    public function testCloseDeliveryNote_Exceptions()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();

        $deliveryNoteMock->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(1));

        $deliveryNoteService->closeDeliveryNote($deliveryNoteMock);
    }


    /**
     *
     *
     */
    public function testOpenDeliveryNote_MethodCheck()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();

        $deliveryNoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $deliveryNoteMock->expects($this->once())
            ->method('setStatus')
            ->with(0)
            ->will($this->returnValue(1));

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($deliveryNoteMock);

        $this->emMock->expects($this->once())
            ->method('flush');

        $deliveryNoteService->openDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only orders with status different than draft could be opened
     *
     */
    public function testOpenDeliveryNote_Exceptions()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();

        $deliveryNoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(0));

        $deliveryNoteService->openDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     *
     */
    public function testCreateInvoiceFromDeliveryNote_MethodCheck()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $deliveryNoteMock->expects($this->at(0))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        /*$deliveryNoteMock->expects($this->at(1))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));*/

        $deliveryNoteMock->expects($this->once())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $deliveryNoteService->createInvoiceFromDeliveryNote($deliveryNoteMock);
    }

    /**
     *
     *
     */
    public function testCreateInvoiceFromDeliveryNote_return()
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $expected = new Invoice();
        $expected->setCommon($commonMock);

        $deliveryNoteMock->expects($this->atLeastOnce())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $deliveryNoteMock->expects($this->once())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $actual = $deliveryNoteService->createInvoiceFromDeliveryNote($deliveryNoteMock);
        $this->assertEquals((array)$expected, (array)$actual, 'Error creating invoice from delivery note');
    }

    public function provider_testCreateInvoiceFromDeliveryNote_Exceptions()
    {
        return array(
            array(0),
            array(2),
            array(4),
            array(5)
        );
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only orders with status closed or partly invoiced could be invoiced
     * @dataProvider provider_testCreateInvoiceFromDeliveryNote_Exceptions
     *
     */
    public function testCreateInvoiceFromDeliveryNote_Exceptions($status)
    {
        $deliveryNoteService = $this->getDeliveryNoteService();

        $deliveryNoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\DeliveryNote')->getMock();

        $deliveryNoteMock->expects($this->at(0))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $deliveryNoteMock->expects($this->at(1))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));


        $deliveryNoteService->createInvoiceFromDeliveryNote($deliveryNoteMock);
    }
}