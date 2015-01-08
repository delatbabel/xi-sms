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
 * Ericsson Ipx SMS Gateway
 *
 * This interface implements GatewayInterface and provides an interface
 * to the Ericsson Ipx SMS Gateway.
 *
 * @reference http://www.ericsson.com/res/ourportfolio/pdf/ipx/100907-ipx-service-description.pdf
 */
class IpxGateway implements GatewayInterface
{
    /**
     * Default socket timeout in seconds
     * IPX definition recommends 600s
     */
    const DEFAULT_SOCKET_TIMEOUT = 10;

    /**
     * The originating address Type Of Number
     */
    const TON_SHORT = '0';
    const TON_ALNUM = '1';
    const TON_MSISDN = '2';

    /**
     * @var \SoapClient
     */
    protected $client = null;

    /**
     * Url to SOAP wsdl.  What should this be?
     *
     * @var string
     */
    protected $wsdlUrl = null;

    /**
     * @var string
     */
    protected $username = null;

    /**
     * @var string
     */
    protected $password = null;

    /**
     * Socket timeout
     * @var int
     */
    protected $timeout = self::DEFAULT_SOCKET_TIMEOUT;

    /**
     * Constructor
     *
     * @param string $username
     * @param string $password
     * @param string $wsdlUrl
     * @param int $timeout socket timeout in seconds
     */
    public function __construct(
        $wsdlUrl,
        $username,
        $password,
        $timeout = self::DEFAULT_SOCKET_TIMEOUT)
    {
        if (!$wsdlUrl || !$username || !$password) {
            throw new \InvalidArgumentException('Invalid IpxGateway configuration');
        }
        $this->wsdlUrl = $wsdlUrl;
        $this->username = $username;
        $this->password = $password;
        $this->timeout = intval($timeout) ? : self::DEFAULT_SOCKET_TIMEOUT;
    }

    /**
     * Send an SMS message.
     *
     * @param \Xi\Sms\SmsMessage $message
     * @return boolean true if send was successful for all receivers
     */
    public function send(SmsMessage $message)
    {
        $result = $this->sendMessage(
            $message->getFrom(),
            $message->getTo(),
            $message->getBody()
        );

        return $result;
    }

    /**
     * @return SoapClient
     */
    protected function getClient()
    {
        if (!$this->client) {
            //create soap client while setting response timeout to 10 minutes
            $this->client = new \SoapClient(
                $this->wsdlUrl,
                array('default_socket_timeout' => $this->timeout)
            );
        }

        return $this->client;
    }

    /**
     * Get SOAP call params
     *
     * @param string $from Sender string is msisdn
     * @param string|array $to Recipients msisdn
     * @param string $body Message body
     * @param array $params Params to override defaults
     *
     * @return array
     */
    protected function getParams($from, $to, $body, $params = array())
    {
        $correlationId = microtime(true);
        $originatingAddress = $from; //If MSISDN
        $originatorTON = $this->determineTON($from);

        $userData = (string) $body;

        //Multiple recipients / distribution list
        if(is_array($to)) {
            $to = implode(';', $to);
        }

        return array_merge(
            array(
                'correlationId' => $correlationId,
                'originatingAddress' => $originatingAddress,
                'originatorTON' => $originatorTON,
                'destinationAddress' => $to,
                'userData' => $userData,
                'userDataHeader' => '#NULL#',
                'DCS' => '-1',
                'PID' => '-1',
                'relativeValidityTime' => '-1',
                'deliveryTime' => '#NULL#',
                'statusReportFlags' => '0',
                'accountName' => '#NULL#',
                'tariffClass' => 'EUR0',
                'VAT' => '-1',
                'referenceId' => '#NULL#',
                'serviceName' => '#NULL#',
                'serviceCategory' => '#NULL#',
                'serviceMetaData' => '#NULL#',
                'campaignName' => '#NULL#',
                'username' => $this->username,
                'password' => $this->password
            ),
            $params
        );
    }

    /**
     * Evaluate originatingAddress to determine the TON
     *
     * @param string $from
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function determineTON($from)
    {
        $ton = self::parseSenderTON($from);
        if (is_null($ton)) {
            throw new \InvalidArgumentException('Invalid sender: '. $from);
        }
        return $ton;
    }

    /**
     * Internal send message function.
     *
     * @param string $from
     * @param string|array $to
     * @param string $body
     * @return bool true on successful send
     */
    protected function sendMessage($from, $to, $body)
    {
        $client = $this->getClient();

        //Handle multipart messages
        if(strlen($body) > 160) {
            $parts = str_split($body, 154);
            $messageCount = count($parts);

            foreach($parts as $index => $body) {
                $params = array('userDataHeader' => sprintf('0500030F%02d%02d', $messageCount, $index+1));
                $params = $this->getParams($from, $to, $body, $params);
                $result = $client->__soapCall('send', array('request' => $params));
            }
        }
        else {
            $params = $this->getParams($from, $to, $body);
            $result = $client->__soapCall('send', array('request' => $params));
        }


        return $this->checkResult($result);
    }

    /**
     * Check result for response or error.
     *
     * @param mixed $result
     */
    protected function checkResult($result)
    {
        //Successful send should return a messageId
        if (empty($result->messageId)) {
            return false;
        }

        return ( ! empty($result->responseMessage) && $result->responseMessage == 'Success');
    }

    /**
     * parse originatingAddress and try to guess the Type Of Number
     *
     * @param string $sender
     * @return string | null
     */
    public static function parseSenderTON($sender)
    {
        if (preg_match('/^\d{1,8}$/', $sender)) {
            return self::TON_SHORT; //short number is usually 3-8 digits
        } elseif (preg_match('/^\d{8,16}$/', $sender)) {
            return self::TON_MSISDN;
        } elseif (preg_match('/^[\w .]{0,11}$/', $sender)) { //todo SMS alphabet check
            return self::TON_ALNUM;
        } else {
            return null;
        }
    }
}
