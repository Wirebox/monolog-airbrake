<?php

/**
* Monolog Airbrake Handler Tests
*
* @author Oliver Byford <oliver.byford@idioplatform.com>
* @package Idio::MonologHandlers
*/

namespace Idio\MonologHandlers;

use Monolog\Logger;
use Idio\Mocks\AirbrakeClientMock;

class AirbrakeHandlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var AirbrakeClientMock
     */
    protected $client;

    /**
     * @var AirbrakeHandler
     */
    protected $handler;

    /**
     * Set Up
     * 
     * Create a partially mocked handler so that we can 'expect' our calls to
     * Airbrake
     */
    protected function setup()
    {
        $this->handler = new AirbrakeHandler('test', array());
        $this->client = new AirbrakeClientMock();
        $this->handler->setAirbrakeClient($this->client);
    }

    /**
     * Test the handler adapts a monolog record into an airbrake notice
     */
    public function testReturnsNoticeWhenHandlerHandlesRecord()
    {
        $record = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('foo' => 'bar'),
            'datetime' => new \DateTime("@0"),
            'extra' => array('beans' => 'yummy'),
            'message' => 'log',
        );
        
        $this->handler->handle($record);

        $this->assertCount(1, $this->client->noticeHistory);
        $this->assertInstanceOf('\Airbrake\Notice', $this->client->noticeHistory[0]);

        $notice = $this->client->noticeHistory[0]->toArray();

        $this->assertEquals($record['level_name'], $notice['errorClass']);
        $this->assertEquals($record['message'], $notice['errorMessage']);
        $this->assertEquals($record['context'], $notice['extraParameters']['context']);
        $this->assertEquals($record['channel'], $notice['extraParameters']['channel']);
        $this->assertEquals($record['extra'], $notice['extraParameters']['extra']);
        $this->assertInternalType('array', $notice['backtrace']);
        $this->assertGreaterThanOrEqual(1, count($notice['backtrace']));
    }
}
