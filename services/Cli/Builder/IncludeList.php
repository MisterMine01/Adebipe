<?php

class IncludeList
{
    private $include_list = [];

    public function add(string $file)
    {
        $this->include_list[] = $file;
    }

    public function get(): array
    {
        return $this->include_list;
    }

    public function includeList(): string
    {
        $include_list = '';
        foreach ($this->include_list as $file) {
            $include_list .= "require_once __DIR__ . '/$file';\n";
        }
        return $include_list;
    }

}