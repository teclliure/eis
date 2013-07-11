<?php

namespace Teclliure\DashboardBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        $this->getRequest()->setLocale($this->getRequest()->getPreferredLanguage(array('en', 'ca', 'es' )));
        return $this->redirect($this->generateUrl('invoice_list'));
        return $this->render('TeclliureDashboardBundle:Default:index.html.twig');
    }
}
