<?php

/**
 * This file is part of the Xi SMS package.
 *
 * For copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Xi\Sms;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Xi\Sms\Event\Events;
use Xi\Sms\Event\FilterEvent;
use Xi\Sms\Event\SmsMessageEvent;
use Xi\Sms\Filter\FilterInterface;
use Xi\Sms\Gateway\GatewayInterface;

/**
 * SMS service
 *
 * This class encapsulates an SMS service, comprising a gateway interface and
 * an event dispatcher (if required).
 *
 * @example <br />
 *     $myGateway = new MessageBirdGateway('my_apikey'); <br />
 *     $myService = new SmsService($myGateway); <br />
 *     # myService can now be used for sending messages of class SmsMessage <br />
 *     $myMessage = SmsMessage('Hello World', '0400 999 999', array('0411 888 8888')); <br />
 *     $myService->send($myMessage);
 */
class SmsService
{
    /**
     * @var EventDispatcherInterface
     */
    private $ed;

    /**
     * @var FilterInterface[]
     */
    private $filters = [];

    /**
     * Constructor
     *
     * Note that the Event Dispatcher is not required, and if none is provided then
     * an empty EventDispatcher will be used.
     * 
     * @param GatewayInterface $gateway
     * @param EventDispatcherInterface $ed
     */
    public function __construct(GatewayInterface $gateway, EventDispatcherInterface $ed = null)
    {
        if (!$ed) {
            $ed = new EventDispatcher();
        }

        $this->gateway = $gateway;
        $this->ed = $ed;
    }

    /**
     * Adds a filter
     *
     * @param FilterInterface $filter
     * @return SmsService
     */
    public function addFilter(FilterInterface $filter)
    {
        $this->filters[] = $filter;
        return $this;
    }

    /**
     * Gets the array of filters
     *
     * @return FilterInterface[]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Sends the message.
     *
     * Returns true if the message was sent successfully, false if ther was an error.
     *
     * @param SmsMessage $message
     * @return bool
     */
    public function send(SmsMessage $message)
    {

        foreach ($this->getFilters() as $filter) {
            /** @var FilterInterface $filter */
            if (!$filter->accept($message)) {
                $event = new FilterEvent($message, $filter);
                $this->ed->dispatch(Events::FILTER_DENY, $event);
                return false;
            }
        }

        $ret = $this->gateway->send($message);

        if ($ret) {
            $event = new SmsMessageEvent($message);
            $this->ed->dispatch(Events::SEND, $event);
        }

        return $ret;
    }
}

