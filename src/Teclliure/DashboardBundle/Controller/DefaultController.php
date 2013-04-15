<?php

namespace Teclliure\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('TeclliureDashboardBundle:Default:index.html.twig');
    }
}
