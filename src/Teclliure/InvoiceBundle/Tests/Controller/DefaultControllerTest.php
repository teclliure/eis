<?php

namespace Teclliure\InvoiceBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testLogin()
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
}
