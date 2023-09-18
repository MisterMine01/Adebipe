<?php

class IncludeList
{
    private $include_list = [];

    private $function_list = [];

    public function add(string $file)
    {
        $this->include_list[] = $file;
    }

    public function get(): array
    {
        return $this->include_list;
    }

    public function addFunction(string $function)
    {
        $this->function_list[] = $function;
    }

    public function getFunction(): array
    {
        return $this->function_list;
    }

    public function includeList(): string
    {
        $include_list = '';
        foreach ($this->include_list as $file) {
            $include_list .= "require_once __DIR__ . '/$file';\n";
        }
        return $include_list;
    }

    public function functionList(): string
    {
        $function_list = '';
        foreach ($this->function_list as $function) {
            $function_list .= $function . "\n";
        }
        return $function_list;
    }

}