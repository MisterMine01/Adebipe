<?php

use Adebipe\Services\ErrorSenderInterface;
use Adebipe\Services\Logger;

class FalseSentryMock
{
    public $isSendErrorCalled = false;

    public function sendError(Logger $logger, array $backtrace): void
    {
        $this->isSendErrorCalled = true;
    }

    public function reset(): void
    {
        $this->isSendErrorCalled = false;
    }
}
