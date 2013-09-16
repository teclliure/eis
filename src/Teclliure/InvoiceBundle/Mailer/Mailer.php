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

use Symfony\Bundle\FrameworkBundle\Templating\EngineInterface;
use Symfony\Component\Routing\RouterInterface;
use Teclliure\InvoiceBundle\Mailer\MailerInterface;

/**
 * @author Marc Montañés <marc@teclliure.net>
 */
class Mailer implements MailerInterface
{
    protected $mailer;
    protected $router;
    protected $templating;
    protected $parameters;

    public function __construct($mailer, RouterInterface $router, EngineInterface $templating, array $parameters)
    {
        $this->mailer = $mailer;
        $this->router = $router;
        $this->templating = $templating;
        $this->parameters = $parameters;
    }

    /**
     * {@inheritdoc}
     */
    public function sendQuoteEmailMessage(Common $common)
    {
        $template = $this->parameters['quote.template'];
        $rendered = $this->templating->render($template, array(
            'common' => $common
        ));
        $this->sendEmailMessage($rendered, $this->parameters['from_email'], $common->getQuote()->getContact()->getEmail());
    }

    /**
     * @param string $renderedTemplate
     * @param string $fromEmail
     * @param string $toEmail
     */
    protected function sendEmailMessage($renderedTemplate, $fromEmail, $toEmail)
    {
        // Render the email, use the first line as the subject, and the rest as the body
        $renderedLines = explode("\n", trim($renderedTemplate));
        $subject = $renderedLines[0];
        $body = implode("\n", array_slice($renderedLines, 1));

        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($fromEmail)
            ->setTo($toEmail)
            ->setBody($body);

        $this->mailer->send($message);
    }
}
