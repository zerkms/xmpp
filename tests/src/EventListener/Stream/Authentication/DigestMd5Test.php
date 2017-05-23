<?php

/**
 * Copyright 2014 Fabian Grutschus. All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice, this
 *   list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 *   this list of conditions and the following disclaimer in the documentation
 *   and/or other materials provided with the distribution.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * The views and conclusions contained in the software and documentation are those
 * of the authors and should not be interpreted as representing official policies,
 * either expressed or implied, of the copyright holders.
 *
 * @author    Fabian Grutschus <f.grutschus@lubyte.de>
 * @copyright 2014 Fabian Grutschus. All rights reserved.
 * @license   BSD
 * @link      http://github.com/fabiang/xmpp
 */

namespace Fabiang\Xmpp\EventListener\Stream\Authentication;

use PHPUnit\Framework\TestCase;
use Fabiang\Xmpp\Event\XMLEvent;
use Fabiang\Xmpp\Connection\Test;
use Fabiang\Xmpp\Options;
use Fabiang\Xmpp\Util\XML;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.1 on 2014-01-27 at 12:11:12.
 *
 * @coversDefaultClass Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5
 */
class DigestMd5Test extends TestCase
{

    /**
     * @var DigestMd5
     */
    protected $object;

    /**
     *
     * @var Test
     */
    protected $connection;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new DigestMd5;
        $this->connection = new Test;

        $options = new Options;
        $options->setConnection($this->connection);
        $this->object->setOptions($options);
        $this->connection->setReady(true);
    }

    /**
     * Test attaching events.
     *
     * @covers ::attachEvents
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Connection\AbstractConnection
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Options
     * @uses Fabiang\Xmpp\Stream\XMLStream
     * @return void
     */
    public function testAttachEvents()
    {
        $this->object->attachEvents();
        $this->assertSame(
            array(
                '*'                                           => array(),
                '{urn:ietf:params:xml:ns:xmpp-sasl}challenge' => array(array($this->object, 'challenge')),
                '{urn:ietf:params:xml:ns:xmpp-sasl}success'   => array(array($this->object, 'success'))
            ),
            $this->connection->getInputStream()->getEventManager()->getEventList()
        );

        $this->assertSame(
            array(
                '*'                                      => array(),
                '{urn:ietf:params:xml:ns:xmpp-sasl}auth' => array(array($this->object, 'auth')),
            ),
            $this->connection->getOutputStream()->getEventManager()->getEventList()
        );
    }

    /**
     * Test authentication.
     *
     * @covers ::authenticate
     * @covers ::setUsername
     * @covers ::getUsername
     * @covers ::setPassword
     * @covers ::getPassword
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Connection\AbstractConnection
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Options
     * @uses Fabiang\Xmpp\Stream\XMLStream
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @return void
     */
    public function testAuthenticate()
    {
        $this->object->authenticate('aaa', 'bbb');

        $this->assertContains(
            '<auth xmlns="urn:ietf:params:xml:ns:xmpp-sasl" mechanism="DIGEST-MD5"/>',
            $this->connection->getBuffer()
        );
        $this->assertSame('aaa', $this->object->getUsername());
        $this->assertSame('bbb', $this->object->getPassword());
    }

    /**
     * Test blocking when authentication element is send.
     *
     * @covers ::auth
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::isBlocking
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Connection\AbstractConnection
     * @uses Fabiang\Xmpp\Options
     * @return void
     */
    public function testAuth()
    {
        $this->assertFalse($this->object->isBlocking());
        $this->object->auth();
        $this->assertTrue($this->object->isBlocking());
    }

    /**
     * Test parsing challenge and sending response.
     *
     * @covers ::challenge
     * @covers ::response
     * @covers ::parseCallenge
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::getUsername
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::setUsername
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::getPassword
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::setPassword
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Connection\AbstractConnection
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Options
     * @uses Fabiang\Xmpp\Stream\XMLStream
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Util\XML
     * @return void
     */
    public function testChallenge()
    {
        $this->object->setUsername('aaa')->setPassword('bbb');
        $this->object->getOptions()->setTo('localhost');

        $document = new \DOMDocument;
        $document->loadXML(
            '<challenge xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'
            . XML::quote(base64_encode(
                'realm="localhost",nonce="abcdefghijklmnopqrstuvw",'
                . 'qop="auth",charset=utf-8,algorithm=md5-sess'
            ))
            . '</challenge>'
        );

        $event = new XMLEvent;
        $event->setParameters(array($document->documentElement));
        $this->object->challenge($event);

        $buffer = $this->connection->getBuffer();
        $this->assertCount(1, $buffer);
        $response = $buffer[0];
        $this->assertRegExp('#^<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl">.+</response>$#', $response);

        $parser = new \DOMDocument;
        $parser->loadXML($response);
        $value = base64_decode($parser->documentElement->textContent);
        $this->assertRegExp(
            '#^username="aaa",realm="localhost",nonce="abcdefghijklmnopqrstuvw",cnonce="[^"]+",nc=00000001,'
            . 'qop=auth,digest-uri="xmpp/localhost",response=[^,]+,charset=utf-8$#',
            $value
        );
    }

    /**
     * Test sending a rspauth challenge.
     *
     * @covers ::challenge
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::parseCallenge
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Connection\AbstractConnection
     * @uses Fabiang\Xmpp\Event\EventManager
     * @uses Fabiang\Xmpp\Options
     * @uses Fabiang\Xmpp\Stream\XMLStream
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Util\XML
     * @return void
     */
    public function testChallengeRspauth()
    {
        $document = new \DOMDocument;
        $document->loadXML(
            '<challenge xmlns="urn:ietf:params:xml:ns:xmpp-sasl">'
            . XML::base64Encode('rspauth=1234567890') . '</challenge>'
        );

        $event = new XMLEvent;
        $event->setParameters(array($document->documentElement));
        $this->object->challenge($event);

        $buffer = $this->connection->getBuffer();
        $response = $buffer[0];
        $this->assertSame('<response xmlns="urn:ietf:params:xml:ns:xmpp-sasl"/>', $response);
    }

    /**
     * Test sending an empty challenge.
     *
     * @covers ::challenge
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::parseCallenge
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Connection\AbstractConnection
     * @uses Fabiang\Xmpp\Event\XMLEvent
     * @uses Fabiang\Xmpp\Event\Event
     * @uses Fabiang\Xmpp\Options
     * @uses Fabiang\Xmpp\Util\XML
     * @expectedException Fabiang\Xmpp\Exception\Stream\AuthenticationErrorException
     * @expectedExceptionMessage Error when receiving challenge: ""
     * @return void
     */
    public function testChallengeEmpty()
    {
        $document = new \DOMDocument;
        $document->loadXML('<challenge xmlns="urn:ietf:params:xml:ns:xmpp-sasl"></challenge>');

        $event = new XMLEvent;
        $event->setParameters(array($document->documentElement));
        $this->object->challenge($event);
    }

    /**
     * Test handling success event.
     *
     * @covers ::success
     * @covers ::isBlocking
     * @uses Fabiang\Xmpp\EventListener\AbstractEventListener
     * @uses Fabiang\Xmpp\Connection\AbstractConnection
     * @uses Fabiang\Xmpp\Options
     * @uses Fabiang\Xmpp\EventListener\Stream\Authentication\DigestMd5::auth
     * @return void
     */
    public function testSuccess()
    {
        $this->object->auth();
        $this->assertTrue($this->object->isBlocking());
        $this->object->success();
        $this->assertFalse($this->object->isBlocking());
    }
}
