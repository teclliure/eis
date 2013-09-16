<?php

namespace Teclliure\InvoiceBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Teclliure\InvoiceBundle\Event\CommonEvent;
use Teclliure\InvoiceBundle\CommonEvents;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Craue\ConfigBundle\Util\Config;
use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;

class CommonMailerSubscriber implements EventSubscriberInterface
{
    private $fromEmail;

    private $mailer;

    private $translator;

    private $templating;

    private $config;

    public function __construct(\Swift_Mailer $mailer, Translator $translator, LoggableGenerator $pdfGenerator, Config $config, $fromEmail)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->pdfGenerator = $pdfGenerator;
        $this->config = $config;
        $this->templating = $templating;
        $this->fromEmail = $fromEmail;
    }

    public static function getSubscribedEvents()
    {
        return array(
            CommonEvents::INVOICE_CLOSED => array('onInvoiceClosed', -5),
            CommonEvents::QUOTE_CLOSED => array('onQuoteClosed', -5),
            CommonEvents::DELIVERY_NOTE_CLOSED => array('onDeliveryNoteClosed', -5)
        );
    }

    public function onInvoiceClosed(CommonEvent $event)
    {
        $common = $event->getCommon();

        $html = $this->renderView('TeclliureInvoiceBundle:Invoice:invoicePrint.html.twig', array(
            'common' => $common,
            'config' => $this->config->all(),
            'print'  => true
        ));

        $tmpFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'invoice'.$common->getInvoice()->getIssueDate()->format('d-m-Y').$common->getInvoice()->getNumber().'.pdf';
        $this->pdfGenerator->generateFromHtml($html, $tmpFile);

        // Send an email to contact
        $message = Swift_Message::newInstance()
            ->setSubject($this->translator->trans('Invoice created').' '.$common->getInvoice()->getNumber())
            ->setFrom($this->fromEmail)
            ->setTo($common->getInvoice()->getContactEmail())
            ->setBody($this->translator->trans("Hello %name%, an new invoiced is avaliable to you. Open attached PDF file to get more info.", array('%name%'=>$common->getInvoice()->getContactName())))
            ->attach(Swift_Attachment::fromPath($tmpFile));

        $this->mailer->send($message);

        unlink($tmpFile);
    }
}