<?php

/*
 * This file is part of the TeclliureInvoiceBundle package.
 *
 * (c) Marc Montañés <http://www.teclliure.net/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Teclliure\InvoiceBundle\Mailer;

use Teclliure\InvoiceBundle\Entity\Common;
use Symfony\Component\Routing\RouterInterface;
use Teclliure\InvoiceBundle\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Craue\ConfigBundle\Util\Config;
use Knp\Bundle\SnappyBundle\Snappy\LoggableGenerator;
use Symfony\Component\Templating\EngineInterface;

/**
 * @author Marc Montañés <marc@teclliure.net>
 */
class Mailer implements MailerInterface
{
    private $fromEmail;

    private $mailer;

    private $transport; // swiftmailer.transport.real

    private $translator;

    private $templating;

    private $config;

    public function __construct(\Swift_Mailer $mailer, \Swift_Transport $transport, Translator $translator, LoggableGenerator $pdfGenerator, EngineInterface $templating, Config $config, $fromEmail)
    {
        $this->mailer = $mailer;
        $this->transport = $transport;
        $this->pdfGenerator = $pdfGenerator;
        $this->translator = $translator;
        $this->templating = $templating;
        $this->config = $config;
        $this->fromEmail = $fromEmail;
    }

    /**
     * {@inheritdoc}
     */
    public function sendInvoiceEmailMessage(Common $common)
    {
        if ($common->getInvoice()->getContactEmail()) {
            $html = $this->templating->render('TeclliureInvoiceBundle:Invoice:invoicePrint.html.twig', array(
                'common' => $common,
                'config' => $this->config->all(),
                'print'  => true
            ));

            $tmpFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'invoice'.$common->getInvoice()->getNumber().'.pdf';
            unlink($tmpFile);
            $this->pdfGenerator->generateFromHtml($html, $tmpFile);

            $subject = $this->translator->trans('Invoice created').' '.$common->getInvoice()->getNumber();
            $body = $this->templating->render('TeclliureInvoiceBundle:Invoice:sendMail.html.twig', array('common'=>$common));

            $this->sendEmailMessage($subject, $body, $this->fromEmail, $common->getInvoice()->getContactEmail(), $tmpFile);

            unlink($tmpFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendQuoteEmailMessage(Common $common)
    {
        if ($common->getQuote()->getContactEmail()) {
            $html = $this->templating->render('TeclliureInvoiceBundle:Quote:quotePrint.html.twig', array(
                'common' => $common,
                'config' => $this->config->all(),
                'print'  => true
            ));

            $tmpFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'quote'.$common->getQuote()->getNumber().'.pdf';
            unlink($tmpFile);
            $this->pdfGenerator->generateFromHtml($html, $tmpFile);

            $subject = $this->translator->trans('Quote created').' '.$common->getQuote()->getNumber();
            $body = $this->templating->render('TeclliureInvoiceBundle:Quote:sendMail.html.twig', array('common'=>$common));

            $this->sendEmailMessage($subject, $body, $this->fromEmail, $common->getQuote()->getContactEmail(), $tmpFile);

            unlink($tmpFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendDeliveryNoteEmailMessage(Common $common)
    {
        if ($common->getDeliveryNote()->getContactEmail()) {
            $html = $this->templating->render('TeclliureInvoiceBundle:DeliveryNote:deliveryNotePrint.html.twig', array(
                'common' => $common,
                'config' => $this->config->all(),
                'print'  => true
            ));

            $tmpFile = rtrim(sys_get_temp_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'order'.$common->getDeliveryNote()->getNumber().'.pdf';
            unlink($tmpFile);
            $this->pdfGenerator->generateFromHtml($html, $tmpFile);

            $subject = $this->translator->trans('Order created').' '.$common->getDeliveryNote()->getNumber();
            $body = $this->templating->render('TeclliureInvoiceBundle:DeliveryNote:sendMail.html.twig', array('common'=>$common));

            $this->sendEmailMessage($subject, $body, $this->fromEmail, $common->getDeliveryNote()->getContactEmail(), $tmpFile);

            unlink($tmpFile);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function sendPaymentMessage(Common $common) {
        // TODO
    }

    /**
     * @param string $body
     * @param string $fromEmail
     * @param string $toEmail
     * @param string $attach
     */
    protected function sendEmailMessage($subject, $body, $fromEmail, $toEmail, $attach = nulll)
    {
        // Send an email to contact
        $message = $this->mailer->createMessage()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body);

        if ($attach) {
            $message->attach(\Swift_Attachment::fromPath($attach));
        }

        $this->mailer->send($message);

        // We clean queue to allow attach delete
        $spool = $this->mailer->getTransport()->getSpool();
        $spool->flushQueue($this->transport);
    }
}