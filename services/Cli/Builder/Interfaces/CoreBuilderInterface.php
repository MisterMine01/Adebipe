<?php

interface CoreBuilderInterface
{

    public function includeFolder(string $folder): array;

    public function getService(string $service): mixed;

    public function getServiceFunctionName(string $service): string;

}