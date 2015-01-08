<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms;

/**
 * SMS message
 *
 * This class encapsulates an SMS message that can be sent out via
 * the SMS service.
 *
 * @example <br />
 *     $myMessage = new SmsMessage('Hello World', '0400 999 999', array('0411 888 8888')); <br />
 *     $result = $myService->send($myMessage);
 */
class SmsMessage
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $from;

    /**
     * @var array
     */
    private $to = array();

    /**
     * @param string $body
     * @param string $from
     * @param array|string $to
     */
    public function __construct($body = null, $from = null, $to = array())
    {
        $this->body = $body;
        $this->from = $from;

        if ($to) {
            $this->setTo($to);
        }
    }

    /**
     * Sets message body
     *
     * @param string $body
     * @return void
     */
    public function setBody($body)
    {
        $this->body = $body;
    }

    /**
     * Gets message body
     *
     * @return null|string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Sets receiver or an array of receivers
     *
     * @param string|array $to
     * @return void
     */
    public function setTo($to)
    {
        if (!is_array($to)) {
            $to = array($to);
        }
        $this->to = $to;
    }

    /**
     * Gets receiver array
     *
     * @return array
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * Adds one receiver
     *
     * @param string $to
     * @return void
     */
    public function addTo($to)
    {
        $this->to[] = $to;
    }

    /**
     * Sets From address
     *
     * @param string $from
     */
    public function setFrom($from)
    {
        $this->from = $from;
    }

    /**
     * Gets From address
     *
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }
}
