<?php

namespace Teclliure\InvoiceBundle\Tests\Service;

use Teclliure\InvoiceBundle\Entity\DeliveryNote;
use Teclliure\InvoiceBundle\Service\QuoteService;
use Teclliure\InvoiceBundle\Entity\Quote;
use Teclliure\InvoiceBundle\Entity\Common;
use Teclliure\InvoiceBundle\CommonEvents;
use Teclliure\InvoiceBundle\Event\CommonEvent;
use Teclliure\InvoiceBundle\Event\QuoteEvent;
use Teclliure\InvoiceBundle\Event\InvoiceEvent;
use Symfony\Component\Security\Acl\Exception\Exception;

class QuoteServiceTest extends \PHPUnit_Framework_TestCase
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

    protected function getQuoteService() {
        $this->emMock = $this->getEntityManagerMock();
        $this->configMock = $this->getConfigMock();
        $this->eventDispatcherMock = $this->getEventDispatcherMock();
        $this->customCheckerMock = $this->getCustomCheckerMock();

        return new QuoteService($this->emMock, $this->configMock, $this->eventDispatcherMock, $this->customCheckerMock);
    }

    protected function common_getQuotes($queryBuilderMock, $queryMock) {
        $this->emMock->expects($this->once())
          ->method('createQueryBuilder')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('select')
          ->with('q, c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('from')
          ->with('TeclliureInvoiceBundle:Quote','q')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('innerJoin')
          ->with('q.common','c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('getQuery')
          ->will($this->returnValue($queryMock));
    }

    public function testGetQuotes_noFiltersNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $expected = array();

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $quoteService = $this->getQuoteService();
        $quoteService->setPaginator($paginatorMock);

        $this->common_getQuotes($queryBuilderMock, $queryMock);

        $paginatorMock->expects($this->once())
          ->method('paginate')
          ->with($queryMock, $page, $limit)
          ->will($this->returnValue($expected));
        ;

        $queryBuilderMock->expects($this->never())
            ->method('where');

        $actual = $quoteService->getQuotes($limit, $page, array(), null);
        $this->assertEquals($expected, $actual, 'Error getting quotes' );
    }

    public function testGetQuotes_noFiltersNoSortWithLimitNoPage_methodCalls()
    {
        $page = null;
        $limit = 15;
        $expected = array();

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $quoteService = $this->getQuoteService();
        $quoteService->setPaginator($paginatorMock);

        $this->common_getQuotes($queryBuilderMock, $queryMock);

        $queryMock->expects($this->once())
          ->method('getResult')
          ->will($this->returnValue($expected));

        $actual = $quoteService->getQuotes($limit, $page, array(), null);
        $this->assertEquals($expected, $actual, 'Error getting quotes' );
    }

    public function testGetQuotes_filtersNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $filters = array('search'=>'testSearch', 'q_number'=>23213);

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $quoteService = $this->getQuoteService();
        $quoteService->setPaginator($paginatorMock);

        $this->common_getQuotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('where')
          ->with('q.number LIKE :search OR c.customer_name LIKE :search2')
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
          ->with('q.number'.' LIKE :where'.'q_number')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'q_number', '%'.'23213'.'%')
          ->will($this->returnValue($queryBuilderMock));

        $quoteService->getQuotes($limit, $page, $filters, null);
    }

    public function testGetQuotes_filtersObjectNoSortWithLimitWithPage_methodCalls()
    {
        $customerMock = $this->getCustomerMock();
        $page = 2;
        $limit = 15;
        $filters = array('q_customer'=>$customerMock);

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $quoteService = $this->getQuoteService();
        $quoteService->setPaginator($paginatorMock);

        $this->common_getQuotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('q.customer'.' = :where'.'q_customer')
          ->will($this->returnValue($queryBuilderMock));

        $customerMock->expects($this->once())
          ->method('getId')
          ->will($this->returnValue(111));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'q_customer', '111')
          ->will($this->returnValue($queryBuilderMock));

        $quoteService->getQuotes($limit, $page, $filters, null);
    }

    public function testGetQuotes_filtersArrayNoSortWithLimitWithPage_methodCalls()
    {
        $page = 2;
        $limit = 15;
        $filters = array('q_customer'=>array(1, 3, 2, 44));

        $queryMock = $this->getQueryMock();
        $queryBuilderMock = $this->getQueryBuilderMock();
        $paginatorMock = $this->getPaginatorMock();

        $quoteService = $this->getQuoteService();
        $quoteService->setPaginator($paginatorMock);

        $this->common_getQuotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->once())
          ->method('andWhere')
          ->with('q.customer'.' IN (:whereq_customer)')
          ->will($this->returnValue($queryBuilderMock));


        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('where'.'q_customer', array(1, 3, 2, 44))
          ->will($this->returnValue($queryBuilderMock));

        $quoteService->getQuotes($limit, $page, $filters, null);
    }

    public function testGetQuotes_noFiltersSortWithLimitNoPage_methodCalls()
    {
        $page = null;
        $limit = 15;
        $sort = array(array('sort'=>'q.customer', 'sortOrder'=>'DESC'));

        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryMock = $this->getQueryMock();
        $quoteService = $this->getQuoteService();

        $this->common_getQuotes($queryBuilderMock, $queryMock);

        $queryBuilderMock->expects($this->at(3))
          ->method('addOrderBy')
          ->with('q.customer', 'DESC')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->at(4))
          ->method('addOrderBy')
          ->with('q.created', 'DESC')
          ->will($this->returnValue($queryBuilderMock));

        $quoteService->getQuotes($limit, $page, array(), $sort);
    }

    public function testGetQuote_methodCalls()
    {
        $queryBuilderMock = $this->getQueryBuilderMock();
        $queryMock = $this->getQueryMock();
        $quoteService = $this->getQuoteService();

        $quote = new Quote();

        $this->emMock->expects($this->once())
          ->method('createQueryBuilder')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('select')
          ->with('q, c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('from')
          ->with('TeclliureInvoiceBundle:Quote','q')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('where')
          ->with('q.id = :quoteId')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('setParameter')
          ->with('quoteId', $quote->getId())
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('innerJoin')
          ->with('q.common','c')
          ->will($this->returnValue($queryBuilderMock));

        $queryBuilderMock->expects($this->once())
          ->method('getQuery')
          ->with()
          ->will($this->returnValue($queryMock));

        $queryMock->expects($this->once())
          ->method('getOneOrNullResult')
          ->with()
          ->will($this->returnValue($quote));

        $actual = $quoteService->getQuote($quote->getId());
        $this->assertEquals($quote, $actual, 'Error getting quote' );
    }

    public function testCreateQuote_methodCalls()
    {
        $quoteService = $this->getQuoteService();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();
        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $expected = new Quote();

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
            ->with('default_footnote_quote')
            ->will($this->returnValue('Footnote sdsd'));

        $quoteMock->expects($this->once())
            ->method('setFootnote')
            ->with('Footnote sdsd')
            ->will($this->returnValue(null));

        $this->configMock->expects($this->at(3))
            ->method('get')
            ->with('default_footnote_quote')
            ->will($this->returnValue('Footnote sdsd'));
        // $quoteService->putDefaults($expected);

        $quoteService->createQuote($quoteMock, $commonMock);
    }

    public function testCreateQuote_correctOutput()
    {
        $quoteService = $this->getQuoteService();
        $expected = new Quote();
        $common = new Common();
        $expected->setCommon($common);

        $actual = $quoteService->createQuote();
        $this->assertEquals((array)$expected, (array)$actual, 'Error creating quote');
    }

    public function testDuplicate_correctOutput()
    {
        $quoteService = $this->getQuoteService();

        $expected = new Quote();
        $expected->setCommon(new Common());
        $expected->setStatus(2);

        $actual = $quoteService->duplicateQuote($expected);
        $expected->setStatus(0);

        $this->assertEquals((array)$expected, (array)$actual, 'Error putting defaults to quote');
    }

    /**
     *
     *
     */
    public function testSaveQuote_methodsCall_createNumber()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $quoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $quoteMock->expects($this->any())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(0));

        $quoteMock->expects($this->once())
            ->method('getNumber')
            ->with()
            ->will($this->returnValue(23));

        $quoteService->saveQuote($quoteMock);
    }

    /**
     *
     *
     */
    public function testSaveQuote_methodsCall()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $quoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $quoteMock->expects($this->any())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(0));

        $quoteMock->expects($this->once())
            ->method('getNumber')
            ->with()
            ->will($this->returnValue('Quote Nb'));

        $quoteService->saveQuote($quoteMock);
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only quotes with status draft could be edited
     *
     */
    public function testSaveQuote_methodsCallException()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $quoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));


        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $quoteService->saveQuote($quoteMock);
    }

    /**
     *
     *
     */
    public function testSaveQuote_EventsLaunch()
    {
        $quoteService = $this->getQuoteService();

        $queryMock = $this->getQueryMock();
        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();
        $connectionMock = $this->getMockBuilder('\Doctrine\DBAL\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $quoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(null));

        $quoteMock->expects($this->once())
            ->method('getNumber')
            ->with()
            ->will($this->returnValue(null));

        $quoteMock->expects($this->once())
            ->method('setNumber')
            ->with()
            ->will($this->returnValue(4));

        $this->emMock->expects($this->atLeastOnce())
            ->method('getConnection')
            ->with()
            ->will($this->returnValue($connectionMock));

        $connectionMock->expects($this->at(0))
            ->method('exec')
            ->with('LOCK TABLE quote q0_ WRITE;');

        $connectionMock->expects($this->at(1))
            ->method('exec')
            ->with('UNLOCK TABLES;');

        $this->emMock->expects($this->once())
            ->method('createQuery')
            ->with('SELECT MAX(SUBSTRING(q.number, 6)) as number FROM TeclliureInvoiceBundle:Quote q
        WHERE q.created >= :startDate AND q.created < :endDate ORDER BY q.number desc')
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
            ->with(CommonEvents::QUOTE_PRE_SAVED, new QuoteEvent($quoteMock));

        $this->eventDispatcherMock->expects($this->at(1))
            ->method('dispatch')
            ->with(CommonEvents::QUOTE_SAVED, new QuoteEvent($quoteMock));

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($quoteMock);

        $this->emMock->expects($this->once())
            ->method('flush');

        $quoteService->saveQuote($quoteMock);
    }

    /**
     *
     *
     */
    public function testCloseQuote_EventsLaunch()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $quoteMock->expects($this->once())
            ->method('setStatus')
            ->with(1);

        $quoteMock->expects($this->any())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));

        $this->eventDispatcherMock->expects($this->at(0))
            ->method('dispatch')
            ->with(CommonEvents::QUOTE_CLOSED, new CommonEvent($commonMock));

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($quoteMock);

        $this->emMock->expects($this->once())
            ->method('flush');

        $quoteService->closeQuote($quoteMock);
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only quotes with status draft could be closed
     *
     */
    public function testCloseQuote_Exceptions()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->will($this->returnValue(1));

        $quoteService->closeQuote($quoteMock);
    }


    /**
     *
     *
     */
    public function testOpenQuote_MethodCheck()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $quoteMock->expects($this->once())
            ->method('setStatus')
            ->with(0)
            ->will($this->returnValue(1));

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($quoteMock);

        $this->emMock->expects($this->once())
            ->method('flush');

        $quoteService->openQuote($quoteMock);
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only quotes with status different than draft could be opened
     *
     */
    public function testOpenQuote_Exceptions()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(0));

        $quoteService->openQuote($quoteMock);
    }

    /**
     *
     *
     */
    public function testDenyQuote_MethodCheck()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $quoteMock->expects($this->once())
            ->method('setStatus')
            ->with(2);

        $this->emMock->expects($this->once())
            ->method('persist')
            ->with($quoteMock);

        $this->emMock->expects($this->once())
            ->method('flush');

        $quoteService->denyQuote($quoteMock);
    }

    public function provider_testDenyQuote_Exceptions()
    {
        return array(
          array(2),
          array(3)
        );
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only quotes with status draft or pending could be rejected
     * @dataProvider provider_testDenyQuote_Exceptions
     *
     */
    public function testDenyQuote_Exceptions($status)
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $quoteService->denyQuote($quoteMock);
    }

    /**
     *
     *
     */
    public function testCreateDeliveryNoteFromQuote_MethodCheck()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $quoteMock->expects($this->once())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));


        $quoteService->createDeliveryNoteFromQuote($quoteMock);
    }

    public function provider_testCreateDeliveryNoteFromQuote_Exceptions()
    {
        return array(
            array(0),
            array(2),
            array(3),
            array(4),
            array(5),
            array(6)
        );
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only quotes with status pending could be ordered
     * @dataProvider provider_testCreateDeliveryNoteFromQuote_Exceptions
     *
     */
    public function testCreateDeliveryNoteFromQuote_Exceptions($status)
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();

        $quoteMock->expects($this->once())
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $quoteService->createDeliveryNoteFromQuote($quoteMock);
    }

    /**
     *
     *
     */
    public function testCreateInvoiceFromQuote_MethodCheck()
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();
        $commonMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Common')->getMock();

        $quoteMock->expects($this->at(0))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $quoteMock->expects($this->at(1))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue(1));

        $quoteMock->expects($this->once())
            ->method('getCommon')
            ->with()
            ->will($this->returnValue($commonMock));


        $quoteService->createInvoiceFromQuote($quoteMock);
    }

    public function provider_testCreateInvoiceFromQuote_Exceptions()
    {
        return array(
            array(0),
            array(2),
            array(3),
            array(4),
            array(6)
        );
    }

    /**
     *
     * @expectedException Exception
     * @expectedExceptionMessage Only quotes with status pending or partly invoiced could be invoiced
     * @dataProvider provider_testCreateInvoiceFromQuote_Exceptions
     *
     */
    public function testCreateInvoiceFromQuote_Exceptions($status)
    {
        $quoteService = $this->getQuoteService();

        $quoteMock = $this->getMockBuilder('\Teclliure\InvoiceBundle\Entity\Quote')->getMock();

        $quoteMock->expects($this->at(0))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $quoteMock->expects($this->at(1))
            ->method('getStatus')
            ->with()
            ->will($this->returnValue($status));

        $quoteService->createInvoiceFromQuote($quoteMock);
    }
}

