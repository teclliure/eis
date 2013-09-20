<?php

namespace Teclliure\InvoiceBundle\Listener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Teclliure\InvoiceBundle\Event\CommonEvent;
use Teclliure\InvoiceBundle\CommonEvents;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Craue\ConfigBundle\Util\Config;
use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Symfony\Component\Templating\EngineInterface;

class CommonMailerSubscriber implements EventSubscriberInterface
{
    private $fromEmail;

    private $mailer;

    private $transport; // swiftmailer.transport.real

    private $translator;

    private $templating;

    private $config;

    public function __construct(\Swift_Mailer $mailer, \Swift_Transport $transport,Translator $translator, LoggableGenerator $pdfGenerator, EngineInterface $templating, Config $config, $fromEmail)
    {
        $this->mailer = $mailer;
        $this->transport = $transport;
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
        if ($common->getInvoice()->getContactEmail()) {
            $html = $this->templating->render('TeclliureInvoiceBundle:Invoice:invoicePrint.html.twig', array(
                'common' => $common,
                'config' => $this->config->all(),
                'print'  => true
            ));

            $tmpFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'invoice'.$common->getInvoice()->getNumber().'.pdf';
            unlink($tmpFile);
            $this->pdfGenerator->generateFromHtml($html, $tmpFile);

            // Send an email to contact
            $message = $this->mailer->createMessage()
                ->setSubject($this->translator->trans('Invoice created').' '.$common->getInvoice()->getNumber())
                ->setFrom($this->fromEmail)
                ->setTo($common->getInvoice()->getContactEmail())
                ->setBody($this->translator->trans("Hello %name%, an new invoiced is avaliable to you. Open attached PDF file to get more info.".$tmpFile, array('%name%'=>$common->getInvoice()->getContactName())))
                ->attach(\Swift_Attachment::fromPath($tmpFile));

            $this->mailer->send($message);

            // We clean queue to allow attach delete
            $spool = $this->mailer->getTransport()->getSpool();
            $spool->flushQueue($this->transport);

            unlink($tmpFile);
        }
    }

    public function onQuoteClosed(CommonEvent $event)
    {
        $common = $event->getCommon();
        if ($common->getQuote()->getContactEmail()) {
            $html = $this->templating->render('TeclliureInvoiceBundle:Quote:quotePrint.html.twig', array(
                'common' => $common,
                'config' => $this->config->all(),
                'print'  => true
            ));

            $tmpFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'quote'.$common->getQuote()->getNumber().'.pdf';
            unlink($tmpFile);
            $this->pdfGenerator->generateFromHtml($html, $tmpFile);

            // Send an email to contact
            $message = $this->mailer->createMessage()
                ->setSubject($this->translator->trans('Quote created').' '.$common->getQuote()->getNumber())
                ->setFrom($this->fromEmail)
                ->setTo($common->getQuote()->getContactEmail())
                ->setBody($this->translator->trans("Hello %name%, an new Quote is avaliable to you. Open attached PDF file to get more info.".$tmpFile, array('%name%'=>$common->getQuote()->getContactName())))
                ->attach(\Swift_Attachment::fromPath($tmpFile));

            $this->mailer->send($message);

            // We clean queue to allow attach delete
            $spool = $this->mailer->getTransport()->getSpool();
            $spool->flushQueue($this->transport);

            unlink($tmpFile);
        }
    }

    public function onDeliveryNoteClosed(CommonEvent $event)
    {
        $common = $event->getCommon();
        if ($common->getDeliveryNote()->getContactEmail()) {
            $html = $this->templating->render('TeclliureInvoiceBundle:DeliveryNote:deliveryNotePrint.html.twig', array(
                'common' => $common,
                'config' => $this->config->all(),
                'print'  => true
            ));

            $tmpFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'deliveryNote'.$common->getDeliveryNote()->getNumber().'.pdf';
            unlink($tmpFile);
            $this->pdfGenerator->generateFromHtml($html, $tmpFile);

            // Send an email to contact
            $message = $this->mailer->createMessage()
                ->setSubject($this->translator->trans('Order created').' '.$common->getDeliveryNote()->getNumber())
                ->setFrom($this->fromEmail)
                ->setTo($common->getDeliveryNote()->getContactEmail())
                ->setBody($this->translator->trans("Hello %name%, an new Order is avaliable to you. Open attached PDF file to get more info.".$tmpFile, array('%name%'=>$common->getDeliveryNote()->getContactName())))
                ->attach(\Swift_Attachment::fromPath($tmpFile));

            $this->mailer->send($message);

            // We clean queue to allow attach delete
            $spool = $this->mailer->getTransport()->getSpool();
            $spool->flushQueue($this->transport);

            unlink($tmpFile);
        }
    }
}