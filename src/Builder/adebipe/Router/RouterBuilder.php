<?php

class RouterBuilder implements BuilderInterface
{
    public function includeFiles(): array
    {
        return [];
    }

    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {

        $file_code = file_get_contents(__DIR__ . '/Router.php');
        $file_code = str_replace("[\"MIME TYPES GO HERE\"];", $this->getMimeCode(), $file_code);
        file_put_contents($tmp_file, $file_code);
    }

    private function getMimeCode(): string
    {
        $mime = json_decode(file_get_contents(__DIR__ . '/mime.json'), true);
        return var_export($mime, true) . ';';
    }
}
