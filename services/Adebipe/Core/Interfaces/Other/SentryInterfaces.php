<?php

namespace Adebipe\Services;

/**
 * Interface for sending error to a tierce service
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface ErrorSenderInterface
{
    /**
     * Send an error to the tierce service
     *
     * @param Logger $logger    The logger to use
     * @param array  $backtrace The backtrace of the error
     *
     * @return void
     */
    public function sendError(Logger $logger, array $backtrace): void;
}
