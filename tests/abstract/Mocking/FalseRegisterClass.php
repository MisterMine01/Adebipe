<?php

use Adebipe\Services\Interfaces\RegisterServiceInterface;

class FalseRegisterClass implements RegisterServiceInterface
{
    public static function getClass()
    {
        return new self();
    }

    private function __construct()
    {
    }

    public function noType($not)
    {
    }

    public function otherType(int $not, string $fromName, ?string $wantNull, string $default = "default")
    {
        return "testOtherType";
    }

    public function noParamsValid(int $not)
    {
    }
}
