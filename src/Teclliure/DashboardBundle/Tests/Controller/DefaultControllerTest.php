<?php

namespace Teclliure\DashboardBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testLogin()
    {
        $client = static::createClient();

        $crawler = $client->request('GET', '/');

        $this->assertTrue(
            $client->getResponse()->isRedirect('http://localhost/login')
        );

        // $this->assertTrue($client->getResponse()->isSuccessful());
        // $this->assertTrue($crawler->filter('html:contains("input")')->count() == 4);
        // $this->assertCount(4, $crawler->filter('html:contains("input")'));
    }
}
