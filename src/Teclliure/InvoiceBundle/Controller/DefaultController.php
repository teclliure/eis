<?php

namespace Teclliure\InvoiceBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TeclliureInvoiceBundle:Default:index.html.twig', array('name' => $name));
    }
}
