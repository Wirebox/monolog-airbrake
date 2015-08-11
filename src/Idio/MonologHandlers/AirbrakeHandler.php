<?php
namespace Idio\MonologHandlers;

use Airbrake\Client;
use Monolog\Logger;

/**
* Monolog Airbrake Handler
*
* @author Oliver Byford <oliver.byford@idioplatform.com>
* @package Idio::MonologHandlers
*/
class AirbrakeHandler extends \Monolog\Handler\AbstractProcessingHandler
{

    /**
     * @var string Airbrake Token
     */
    protected $strAirbrakeApiToken = false;

    /**
     * @var array Airbrake Configuration
     */
    protected $arrAirbrakeConfig;

    /**
     * @var object Airbrake Client
     */
    protected $objAirbrakeClient = false;

    /**
     * Constructor
     *
     * Stores the Airbrake API token before handing off to the parent constructor
     * to deal with levels and bubbling
     *
     * @param string  $strAirbrakeApiToken Airbrake Project API Token
     * @param integer $level Level above which entries should be logged
     * @param boolean $bubble Whether to bubble to the next handler or not
     */
    public function __construct($token, $config = array(), $level = Logger::ERROR, $bubble = true)
    {
        $this->strAirbrakeApiToken = $token;
        $this->arrAirbrakeConfig = $config;
        parent::__construct($level, $bubble);
    }

    /**
     * @inheritdoc
     */
    protected function write(array $record)
    {
        $this->initialize();

        $this->objAirbrakeClient->notify(
            $this->createAirbrakeNotice(
                array(
                    'errorClass'   => $record['level_name'],
                    'errorMessage' => $this->makeXmlSafe($record['message']),
                    'backtrace'    => debug_backtrace(),
                    'extraParameters' => array(
                            'extra'   => $record['extra'],
                            'context' => $record['context'],
                            'channel' => $record['channel']
                    ),
                )
            )
        );
    }

    /**
     * Make XML Safe
     *
     * @param string $strMessage Message
     * @return string Message with & replaced with &amp;
     */
    protected function makeXmlSafe($strMessage)
    {
        return str_replace('&', '&amp;', $strMessage);
    }

    /**
     * Initialize
     *
     * Create a Airbrake client object if it doesn't exist already
     */
    protected function initialize()
    {
        if ($this->objAirbrakeClient) {
            return;
        } else {
            $this->objAirbrakeClient = $this->getAirbrakeClient();
        }
    }

    /**
     * Get Airbrake Client
     *
     * @return object Airbrake Client
     */
    protected function getAirbrakeClient()
    {
        return new \Airbrake\Client(
            new \Airbrake\Configuration($this->strAirbrakeApiToken, $this->arrAirbrakeConfig)
        );
    }

    /**
     * Set Airbrake Client
     *
     * @param \Airbrake\Client $client
     * @return void
     */
    public function setAirbrakeClient(Client $client)
    {
        $this->objAirbrakeClient = $client;
    }

    /**
     * Create Airbrake Notice
     *
     * @param array Airbrake notice details (errorClass, errorMessage, extraParameters)
     * @return object Airbrake Notice
     */
    protected function createAirbrakeNotice($arrDetails)
    {
        return new \Airbrake\Notice($arrDetails);
    }
}
