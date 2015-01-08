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
 * Mock gateway just stores the sent messages so they can be inspected at will.
 */
class MockGateway implements GatewayInterface
{
    /**
     * @var array
     */
    private $sentMessages = array();

    /**
     * Sends a message.
     *
     * This function actually doesn't send anything, it only stores the messages
     * internally.
     *
     * @see GatewayInterface::send
     */
    public function send(SmsMessage $message)
    {
        $this->sentMessages[] = $message;
    }

    /**
     * Retrieve the stored messages sent by send().
     *
     * @return array
     */
    public function getSentMessages()
    {
        return $this->sentMessages;
    }
}
