<?php


class RouteKeeperBuilder implements BuilderInterface
{
    public function includeFiles(): array
    {
        return [];
    }

    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {
    }
}