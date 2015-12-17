<?php

/*
 * This file is part of the Сáша framework.
 *
 * (c) tchiotludo <http://github.com/tchiotludo>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare (strict_types = 1);

namespace Cawa\Email;

use Cawa\Core\App;
use Cawa\Events\TimerEvent;
use Swift_Events_ResponseEvent;
use Swift_Events_TransportChangeEvent;
use Swift_Events_TransportExceptionEvent;

class Listener implements
    \Swift_Events_CommandListener,
    \Swift_Events_ResponseListener,
    \Swift_Events_SendListener,
    \Swift_Events_TransportChangeListener,
    \Swift_Events_TransportExceptionListener
{
    /**
     * @var array
     */
    private $log = [];

    /**
     * @return void
     */
    public function sendLog()
    {
        if ($this->log) {
            App::logger()->debug(implode('', $this->log));
        }

        $this->log = [];
    }

    /**
     * @var TimerEvent
     */
    private $sendEvent;

    /**
     * Invoked immediately before the Message is sent.
     *
     * @param \Swift_Events_SendEvent $evt
     */
    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        $this->sendEvent = new TimerEvent('email.send');

        $data = [
            'headers' => $evt->getMessage()->getHeaders()->toString(),
            'subject' => $evt->getMessage()->getSubject(),
            'size' => strlen($evt->getMessage()->toString()),
        ];

        $this->sendEvent->addData($data);
    }

    /**
     * Invoked immediately after the Message is sent.
     *
     * @param \Swift_Events_SendEvent $evt
     */
    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
        App::events()->emit($this->sendEvent);
    }

    /**
     * Invoked immediately following a command being sent.
     *
     * @param \Swift_Events_CommandEvent $evt
     */
    public function commandSent(\Swift_Events_CommandEvent $evt)
    {
        $this->log[] = '> ' . $evt->getCommand();
    }

    /**
     * Invoked immediately following a response coming back.
     *
     * @param Swift_Events_ResponseEvent $evt
     */
    public function responseReceived(Swift_Events_ResponseEvent $evt)
    {
        $this->log[] = '< ' . $evt->getResponse();
    }

    /**
     * @var TimerEvent
     */
    private $connectionEvent;

    /**
     * Invoked just before a Transport is started.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStarted(Swift_Events_TransportChangeEvent $evt)
    {
        $data = [
            'transport' =>  get_class($evt->getTransport())
        ];

        if (method_exists($evt->getTransport(), 'getHost')) {
            $data['host'] = $evt->getTransport()->getHost();
        }

        if (method_exists($evt->getTransport(), 'getPort')) {
            $data['port'] = $evt->getTransport()->getPort();
        }

        if (method_exists($evt->getTransport(), 'getUsername')) {
            $data['username'] = $evt->getTransport()->getUsername();
        }

        if (method_exists($evt->getTransport(), 'getAuthMode')) {
            $data['auth'] = $evt->getTransport()->getAuthMode();
        }

        if (method_exists($evt->getTransport(), 'getEncryption')) {
            $data['encryption'] = $evt->getTransport()->getEncryption();
        }

        $this->connectionEvent = new TimerEvent('email.connection', $data);
    }

    /**
     * Invoked immediately after the Transport is started.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function transportStarted(Swift_Events_TransportChangeEvent $evt)
    {
        App::events()->emit($this->connectionEvent);
        $this->connectionEvent = null;
    }

    /**
     * Invoked just before a Transport is stopped.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function beforeTransportStopped(Swift_Events_TransportChangeEvent $evt)
    {
        // Nothing to do
    }

    /**
     * Invoked immediately after the Transport is stopped.
     *
     * @param Swift_Events_TransportChangeEvent $evt
     */
    public function transportStopped(Swift_Events_TransportChangeEvent $evt)
    {
        // Nothing to do
    }

    /**
     * Invoked as a TransportException is thrown in the Transport system.
     *
     * @param Swift_Events_TransportExceptionEvent $evt
     *
     * @throws Exception
     */
    public function exceptionThrown(Swift_Events_TransportExceptionEvent $evt)
    {
        $this->sendLog();

        throw new Exception(
            $evt->getException()->getMessage(),
            $evt->getException()->getCode(),
            1,
            $evt->getException()->getFile(),
            $evt->getException()->getLine(),
            $evt->getException()
        );
    }
}
