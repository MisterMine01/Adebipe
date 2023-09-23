<?php

class IncludeList
{
    private $include_list = [];

    private $function_list = [];

    private $other_list = [];

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

    public function addOther(string $other)
    {
        $this->other_list[] = $other;
    }

    public function getOther(): array
    {
        return $this->other_list;
    }

    public function getFunction(): array
    {
        return $this->function_list;
    }

    private function includeList(): string
    {
        $include_list = '';
        foreach ($this->include_list as $file) {
            $include_list .= "require_once __DIR__ . '/$file';\n";
        }
        return $include_list;
    }

    private function functionList(): string
    {
        $function_list = '';
        foreach ($this->function_list as $function) {
            $function_list .= $function . "\n";
        }
        return $function_list;
    }

    private function otherList(): string
    {
        $other_list = '';
        foreach ($this->other_list as $other) {
            $other_list .= $other . "\n";
        }
        return $other_list;
    }

    public function generate(string $file)
    {
        $content = "<?php";
        $content .= "\n\n";
        $content .= $this->includeList();
        $content .= "\n\n";
        $content .= $this->functionList();
        $content .= "\n\n";
        $content .= $this->otherList();
        file_put_contents($file, $content);
    }
}