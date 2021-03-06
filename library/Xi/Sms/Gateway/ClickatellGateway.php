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
 * Clickatell SMS Gateway
 *
 * This interface implements GatewayInterface and provides an interface
 * to the Clickatell SMS gateway.
 *
 * @reference https://www.clickatell.com/ web site.
 * @reference https://www.clickatell.com/apis-scripts/apis/http-s/ API details for the HTTP/S API
 * @reference https://www.clickatell.com/pricing-and-coverage/message-pricing/ SMS pricing
 * @reference http://en.wikipedia.org/wiki/Clickatell Wikipedia article
 */
class ClickatellGateway extends BaseHttpRequestGateway
{
    /**
     * @var string
     */
    private $apiKey;

    /**
     * @var string
     */
    private $user;

    /**
     * @var string
     */
    private $password;

    /**
     * @var string
     */
    private $endpoint;

    /**
     * Constructor
     *
     * @param string $apiKey
     * @param string $user
     * @param string $password
     * @param string $endpoint
     */
    public function __construct(
        $apiKey,
        $user,
        $password,
        $endpoint = 'https://api.clickatell.com'
    ) {
        $this->apiKey = $apiKey;
        $this->user = $user;
        $this->password = $password;
        $this->endpoint = $endpoint;
    }

    /**
     * Send a message
     *
     * Uses a POST call to a URL like this:
     * http://api.clickatell.com/http/sendmsg?api_id=APIKEY&user=USERNAME&password=PASSWORD&to=123412341234&text=This+Is+A+Test+Message&from=567567567
     *
     * @see GatewayInterface::send
     * @reference https://www.clickatell.com/apis-scripts/apis/http-s/
     * @todo Implement a smarter method of sending (batch)
     */
    public function send(SmsMessage $message)
    {
        $body = urlencode(utf8_decode($message->getBody()));
        $from = urlencode($message->getFrom());

        foreach ($message->getTo() as $to) {
            $url = "{$this->endpoint}/http/sendmsg?api_id={$this->apiKey}&user={$this->user}" .
                "&password={$this->password}&to={$to}&text={$body}&from={$from}";
            $this->getClient()->post($url, array());
        }
        return true;
    }
}
