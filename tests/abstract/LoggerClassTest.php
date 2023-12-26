<?php

class LoggerClassTest
{
    public function logInfo($logger, $message)
    {
        return invokeMethod($logger, "_getString", ["INFO", $message]);
    }
}
