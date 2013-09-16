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

/**
 * @author Marc Montañés <marc@teclliure.net>
 */
interface MailerInterface
{
    /**
     * Send quote email
     *
     * @param Common $common
     *
     * @return void
     */
    public function sendQuoteEmailMessage(Common $common);

    /**
     * Send delivery note email
     *
     * @param Common $common
     *
     * @return void
     */
    public function sendDeliveryNoteEmailMessage(Common $common);

    /**
     * Send invoice email
     *
     * @param Common $common
     *
     * @return void
     */
    public function sendInvoiceEmailMessage(Common $common);

    /**
     * Send payment email
     *
     * @param Common $common
     *
     * @return void
     */
    public function sendPaymentMessage(Common $common);

}
