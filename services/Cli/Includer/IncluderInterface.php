<?php

interface IncluderInterface
{
    public function findAllFile($path): array;

    public function includeAllFile($path): array;

    public function includeFile($path): array;
}
