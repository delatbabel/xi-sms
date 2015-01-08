<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms\Gateway;

use Xi\Sms\SmsMessage;
use XMLWriter;

/**
 * Infobip gateway
 *
 * This interface implements GatewayInterface and provides an interface
 * to the Infobip SMS gateway.  Free trial account available but I am unable
 * to find pricing information on the Infobip web site.  Infobip are based in
 * the UK.
 *
 * @reference http://www.infobip.com/ Company web site.
 * @reference http://www.infobip.com/messaging/ Messaging web site.
 * @reference http://developer.infobip.com/home Developer web site.
 */
class InfobipGateway extends BaseHttpRequestGateway
{
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

    public function __construct(
        $user,
        $password,
        $endpoint = 'https://api2.infobip.com/api'
    ) {
        $this->user = $user;
        $this->password = $password;
        $this->endpoint = $endpoint;
    }

    /**
     * @see GatewayInterface::send
     */
    public function send(SmsMessage $message)
    {
        $writer = new XMLWriter();

        $writer->openMemory();
        $writer->startDocument('1.0', 'UTF-8');

        $writer->startElement('SMS');

        $writer->startElement('authentication');

        $writer->startElement('username');
        $writer->text($this->user);
        $writer->endElement();

        $writer->startElement('password');
        $writer->text($this->password);
        $writer->endElement();

        $writer->endElement();

        $writer->startElement('message');

        $writer->startElement('sender');
        $writer->text($message->getFrom());
        $writer->endElement();

        $writer->startElement('datacoding');
        $writer->text('3');
        $writer->endElement();

        $writer->startElement('text');
        $writer->text(utf8_decode($message->getBody()));
        $writer->endElement();

        $writer->endElement();

        $writer->startElement('recipients');
        foreach ($message->getTo() as $to) {
            $writer->startElement('gsm');
            $writer->text($to);
            $writer->endElement();
        }
        $writer->endElement();

        $writer->endElement();
        $writer->endDocument();

        $requestBody = 'XML=' . preg_replace('/<\?xml.*\?>\n?/', '', $writer->outputMemory());

        $this->getClient()->post($this->endpoint . '/v3/sendsms/xml', array(), $requestBody);

        return true;
    }
}
