<?php

namespace Teclliure\InvoiceBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaxControllerTest extends WebTestCase
{
    public function testCompleteScenario()
    {
        $taxName = 'Test';

        // Create a new client to browse the application
        $client = static::createClient(array(), array(
            'PHP_AUTH_USER' => 'user',
            'PHP_AUTH_PW'   => 'userP',
        ));

        // Create a new entry in the database
        $crawler = $client->request('GET', '/tax/');
        $this->assertEquals(200, $client->getResponse()->getStatusCode(), "Unexpected HTTP status code for GET /tax/");

        $i = 0;
        while ($crawler->filter('td:contains("'.$taxName.' Edit'.'")')->count()) {
            $taxName = $taxName.$i;
            $i++;
        }

        $link = $crawler->selectLink('New tax')->link();
        $crawler = $client->click($link);

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'teclliure_invoicebundle_taxtype[name]'  => $taxName,
            'teclliure_invoicebundle_taxtype[value]' => 29
        ));


        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check data in the list view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("Test")')->count(), 'Missing element td:contains("Test")');

        // Edit the entity
        $crawler = $client->click($crawler->selectLink($taxName)->link());

        $form = $crawler->selectButton('Edit')->form(array(
            'teclliure_invoicebundle_taxtype[name]'  => $taxName.' Edit',
            'teclliure_invoicebundle_taxtype[value]' => 29,
            'teclliure_invoicebundle_taxtype[active]' => 1,
            'teclliure_invoicebundle_taxtype[is_default]' => 1,
        ));

        $client->submit($form);
        // $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->selectLink('Back to list')->link());

        // Check data in the list view
        $this->assertGreaterThan(0, $crawler->filter('td:contains("'.$taxName.' Edit'.'")')->count(), 'Missing element td:contains("'.$taxName.' Edit'.'")');

        // Disable the entity
        $crawler = $client->click($crawler->filter('td:contains("'.$taxName.' Edit'.'")')->parents()->selectLink('Disable')->link());
        $crawler = $client->followRedirect();

        // Enable the entity
        $crawler = $client->click($crawler->filter('td:contains("'.$taxName.' Edit'.'")')->parents()->selectLink('Enable')->link());
        $crawler = $client->followRedirect();

        $crawler = $client->click($crawler->filter('td:contains("'.$taxName.' Edit'.'")')->parents()->selectLink('Delete')->link());
        $crawler = $client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/'.$taxName.'/', $client->getResponse()->getContent());
    }
}