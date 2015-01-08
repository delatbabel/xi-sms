<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms\Gateway;

use Xi\Sms\SmsMessage;
use MessageBird\Client;
use MessageBird\Objects\Message;

/**
 * MessageBird SMS Gateway
 *
 * This interface implements GatewayInterface and provides an interface
 * to the MessageBird SMS gateway.  The MessageBirdGatewayInterface requires
 * a vendor module which can be found on composer as messagebird/php-rest-api.
 * This should be installed along with any other dependencies using
 * <tt>composer install</tt> or <tt>composer update</tt>.
 *
 * @see MessageBird\Client
 *
 * @reference https://www.messagebird.com/ web site.
 * @reference https://www.messagebird.com/en/developers API details
 * @reference https://www.messagebird.com/en/pricing-sms SMS pricing
 */
class MessageBirdGateway implements GatewayInterface
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var Client
     */
    private $client;

    /**
     * Constructor
     *
     * @param string $apiKey
     */
    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    /**
     * Gets the current MessageBird client interface.
     *
     * @return Client
     */
    public function getClient()
    {
        if (!$this->client) {
            $this->client = new Client($this->apiKey);
        }

        return $this->client;
    }

    /**
     * Sets the MessageBird client interface.
     *
     * @param Client $client
     */
    public function setClient(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Sends a message.
     *
     * @see GatewayInterface::send
     * @todo Implement a smarter method of sending (batch)
     */
    public function send(SmsMessage $message)
    {
        $msg = new Message();
        $msg->originator = $message->getFrom();
        $msg->recipients = $message->getTo();
        $msg->body = $message->getBody();

        $this->getClient()->messages->create($msg);

        return true;
    }
}
