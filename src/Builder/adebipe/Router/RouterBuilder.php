<?php

namespace Adebipe\Builder;

/**
 * Builder of the Router
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class RouterBuilder implements BuilderInterface
{
    /**
     * Return the files to include in the build
     *
     * @return array<string> The files to include in the build
     */
    public function includeFiles(): array
    {
        return [];
    }

    /**
     * Build the classes and write them in the temporary file
     *
     * @param string               $tmp_file The temporary file to write the classes
     * @param CoreBuilderInterface $core     The core builder
     *
     * @return void
     */
    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {

        $file_code = file_get_contents(__DIR__ . '/Router.php');
        if ($file_code === false) {
            throw new \Exception("Unable to read the file " . __DIR__ . '/Router.php');
        }
        $file_code = str_replace("[\"MIME TYPES GO HERE\"];", $this->_getMimeCode(), $file_code);
        file_put_contents($tmp_file, $file_code);
    }

    /**
     * Get the code of the mime types
     *
     * @return string The code of the mime types
     */
    private function _getMimeCode(): string
    {
        $content = file_get_contents(__DIR__ . '/mime.json');
        if ($content === false) {
            throw new \Exception("Unable to read the file " . __DIR__ . '/mime.json');
        }
        $mime = json_decode($content, true);
        return var_export($mime, true) . ';';
    }
}
