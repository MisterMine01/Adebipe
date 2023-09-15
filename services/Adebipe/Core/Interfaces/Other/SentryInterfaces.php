<?php

namespace Adebipe\Services;

interface SentryInterfaces
{
    public function sendSentry(Logger $logger, array $backtrace): void;
}