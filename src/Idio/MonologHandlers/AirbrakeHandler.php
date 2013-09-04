<?php
namespace Idio\MonologHandlers;

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
                    'errorMessage' => $record['message'],
                    'extraParameters' => array_merge(
                        $record['extra'],
                        array(
                            'context' => $record['context'],
                            'channel' => $record['channel']
                        )
                    ),
                )
            )
        );
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
