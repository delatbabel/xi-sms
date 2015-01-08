<?php

namespace Xi\Sms\Tests\Gateway;

use Xi\Sms\Gateway\BulkSmsGateway;

class BulkSmsGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @test
     */
    public function sendsCorrectlyFormattedMessageToRightPlace()
    {
        $gateway = new BulkSmsGateway('lussuta', 'tussia', 'http://dr-kobros.com/api');

        $browser = $this->getMockBuilder('Buzz\Browser')
            ->disableOriginalConstructor()
            ->getMock();

        $gateway->setClient($browser);

        $postdata =
            "username=lussuta&password=tussia&message=TestMessage&msisdn=358407682810";

        $browser
            ->expects($this->once())->method('post')
            ->with(
                'http://dr-kobros.com/api',
                array(),
                $postdata
            );

        $message = new \Xi\Sms\SmsMessage(
            'TestMessage',
            '12341234'
        );

        $message->addTo('358407682810');

        $ret = $gateway->send($message);
        $this->assertTrue($ret);
    }
}
