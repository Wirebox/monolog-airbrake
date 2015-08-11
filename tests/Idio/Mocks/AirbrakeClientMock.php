<?php

namespace Idio\Mocks;

use Airbrake\Client;

/**
 *
 **/
class AirbrakeClientMock extends Client
{
    public $noticeHistory = [];

    public function __construct()
    {
        //overwrite parent constructor
    }

    public function notify($notice)
    {
        $this->noticeHistory[] = $notice;
    }
}
