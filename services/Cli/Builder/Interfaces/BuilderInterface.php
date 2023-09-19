<?php

interface BuilderInterface
{
    public function includeFiles(): array;

    public function build(string $tmp_file, CoreBuilderInterface $core): void;
}