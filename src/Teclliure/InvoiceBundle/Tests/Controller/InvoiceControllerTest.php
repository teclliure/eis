<?php

namespace Teclliure\InvoiceBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class InvoiceControllerTest extends WebTestCase
{
    public function testLoginFail()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $response = $client->getResponse();

        $this->assertTrue(
            $response->isRedirect()
        );

        $this->assertRegExp('/\/login$/', $client->getResponse()->headers->get('location'));

        // $this->assertTrue($client->getResponse()->isSuccessful());
        // $this->assertTrue($crawler->filter('html:contains("input")')->count() == 4);
        // $this->assertCount(4, $crawler->filter('html:contains("input")'));
    }

    public function testLoginOk()
    {
      // Create a new client to browse the application
      $client = static::createClient(array(), array(
        'PHP_AUTH_USER' => 'admin',
        'PHP_AUTH_PW'   => 'adminP',
      ));

      // Create a new entry in the database
      $crawler = $client->request('GET', '/en/invoice/list');
      $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Login error on /en/invoice/list");
      $this->assertGreaterThan(
        0,
        $crawler->filter('html:contains("Invoice number")')->count(),
        'Incorrect page'
      );
    }

    /*public function testLoginOkFail()
    {
      // Create a new client to browse the application
      $client = static::createClient(array(), array(
        'PHP_AUTH_USER' => 'admin',
        'PHP_AUTH_PW'   => 'adminP',
      ));

      // Create a new entry in the database
      $crawler = $client->request('GET', '/en/invoice/list');
      $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Login error on /en/invoice/list");
      $this->assertGreaterThan(
        0,
        $crawler->filter('html:contains("Invoice numbersss")')->count(),
        'Incorrect page'
      );
    }*/
}
