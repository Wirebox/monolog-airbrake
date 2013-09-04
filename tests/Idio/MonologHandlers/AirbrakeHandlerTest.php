<?php

/**
* Monolog Airbrake Handler Tests
*
* @author Oliver Byford <oliver.byford@idioplatform.com>
* @package Idio::MonologHandlers
*/

namespace Idio\MonologHandlers;

use Monolog\Logger;

include_once('vendor/autoload.php');

/**
 * @author Rafael Dohms <rafael@doh.ms>
 * @see    https://www.hipchat.com/docs/api
 */
class AirbrakeHandlerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var object Partially mocked handler
     */
    protected $handler;

    /**
     * @var object Mocked Airbrake Client
     */
    protected $airbrake;

    /**
     * Set Up
     * 
     * Create a partially mocked handler so that we can 'expect' our calls to
     * Airbrake
     */
    protected function setUp()
    {
        $this->airbrake = $this->getMockBuilder('\Airbrake\Client')
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->handler = $this->getMockBuilder('\Idio\MonologHandlers\AirbrakeHandler')
                              ->disableOriginalConstructor()
                              ->setMethods(array('getAirbrakeClient', 'createAirbrakeNotice'))
                              ->getMock();

        $this->handler->expects($this->any())
                      ->method('getAirbrakeClient')
                      ->will($this->returnValue($this->airbrake));

        $this->handler->__construct('test', array());
    }

    /**
     * Test that a notice gets sent when an entry needs logging
     */
    public function testNoticeGetsSent()
    {
        $this->handler->expects($this->once())
                      ->method('createAirbrakeNotice')
                      ->with(
                          array(
                             'errorClass' => 'ERROR',
                             'errorMessage' => 'log',
                             'extraParameters' => array(
                                 'channel' => 'meh',
                                 'context' => array()
                             )
                         )
                      )
                      ->will($this->returnValue(new \Airbrake\Notice(array())));

        $this->airbrake->expects($this->once())
                       ->method('notify')
                       ->with($this->isInstanceOf('\Airbrake\Notice'));

        $this->handler->handle(
            array(
                'level' => Logger::ERROR,
                'level_name' => 'ERROR',
                'channel' => 'meh',
                'context' => array(),
                'datetime' => new \DateTime("@0"),
                'extra' => array(),
                'message' => 'log',
            )
        );
    }

    /**
     * Test that extra details are included in the notice which is sent to
     * Airbrake
     */
    public function testNoticeExtraDetails()
    {
        $this->handler->expects($this->once())
                      ->method('createAirbrakeNotice')
                      ->with(
                          array(
                             'errorClass' => 'ERROR',
                             'errorMessage' => 'log',
                             'extraParameters' => array(
                                 'channel' => 'meh',
                                 'context' => array('yummy' => 'beans'),
                                 'beans' => 'yummy'
                             )
                          )
                      )
                      ->will($this->returnValue(new \Airbrake\Notice(array())));

        $this->airbrake->expects($this->once())
                       ->method('notify')
                       ->with($this->isInstanceOf('\Airbrake\Notice'));

        $this->handler->handle(
            array(
                'level' => Logger::ERROR,
                'level_name' => 'ERROR',
                'channel' => 'meh',
                'context' => array('yummy' => 'beans'),
                'datetime' => new \DateTime("@0"),
                'extra' => array('beans' => 'yummy'),
                'message' => 'log',
            )
        );
    }

    /**
     * Test that no notice is sent if the error level is below the threshold
     */
    public function testNoticeBelowThresholdIsNotSent()
    {
        $this->airbrake->expects($this->never())
                       ->method('notify');

        $this->handler->handle(
            array(
                'level' => Logger::DEBUG,
                'level_name' => 'DEBUG',
                'channel' => 'meh',
                'context' => array('from' => 'logger'),
                'datetime' => new \DateTime("@0"),
                'extra' => array('file' => 'test', 'line' => 14),
                'message' => 'log',
            )
        );
    }
}
