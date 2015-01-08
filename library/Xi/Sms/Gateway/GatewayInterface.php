<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms\Gateway;

use Xi\Sms\SmsMessage;

/**
 * SMS Gateway interface
 *
 * This interface implements a standardised basic SMS interface.
 * Every gateway should implement this interface.
 */
interface GatewayInterface
{
    /**
     * Sends a fire and forget type SMS message.
     *
     * @param SmsMessage $message
     */
    public function send(SmsMessage $message);
}
