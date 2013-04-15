<?php

namespace Teclliure\UserBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('TeclliureUserBundle:Default:index.html.twig', array('name' => $name));
    }
}
