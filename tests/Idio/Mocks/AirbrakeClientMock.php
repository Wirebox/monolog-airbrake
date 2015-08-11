<?php

namespace Idio\Mocks;

use Airbrake\Client;

/**
 * 
 **/
class AirbrakeClientMock extends Client
{
    public $noticeHistory = [];

    function __construct()
    {
        //do nothing
    }

    public function notify($notice)
    {
        $this->noticeHistory[] = $notice;
    }
}
