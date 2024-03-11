<?php

use Adebipe\Services\Interfaces\RegisterServiceInterface;

class FalseRegisterClass implements RegisterServiceInterface
{
    public static function getClass()
    {
        return new self();
    }

    /**
     * Test on a private constructor
     */
    private function __construct()
    {
    }


    /**
     * A function with no type on the first parameter
     *
     * @param $not
     *
     * @return void
     */
    public function noType($not): void
    {
    }

    /**
     * A function with type in all parameters and with default value, null and no default value
     */
    public function otherType(int $not, string $fromName, ?string $wantNull, string $default = "default"): string
    {
        return "testOtherType";
    }
}
