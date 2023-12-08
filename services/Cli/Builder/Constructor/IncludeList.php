<?php

namespace Adebipe\Cli\Builder;

/**
 * List of includes, functions and other things to be generated
 * in the services.php file
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class IncludeList
{
    private array $_include_list = [];

    private array $_function_list = [];

    private array $_other_list = [];

    /**
     * Add a file to the include list
     *
     * @param string $file File to include
     *
     * @return void
     */
    public function add(string $file): void
    {
        $this->_include_list[] = $file;
    }

    /**
     * Get the include list
     *
     * @return array<string> List of files to include
     */
    public function get(): array
    {
        return $this->_include_list;
    }

    /**
     * Add a function to the function list
     *
     * @param string $function Function to add
     *
     * @return void
     */
    public function addFunction(string $function): void
    {
        $this->_function_list[] = $function;
    }


    /**
     * Get the function list
     *
     * @return array<string> List of functions to add
     */
    public function getFunction(): array
    {
        return $this->_function_list;
    }

    /**
     * Add a other line in the services.php file
     *
     * @param string $other Other line to add
     *
     * @return void
     */
    public function addOther(string $other): void
    {
        $this->_other_list[] = $other;
    }

    /**
     * Get the other list
     *
     * @return array<string> List of other lines to add
     */
    public function getOther(): array
    {
        return $this->_other_list;
    }


    /**
     * Generate the include list
     *
     * @return string
     */
    private function _includeList(): string
    {
        $include_list = '';
        foreach ($this->_include_list as $file) {
            $include_list .= "require_once __DIR__ . '/$file';\n";
        }
        return $include_list;
    }

    /**
     * Generate the function list
     *
     * @return string
     */
    private function _functionList(): string
    {
        $function_list = '';
        foreach ($this->_function_list as $function) {
            $function_list .= $function . "\n";
        }
        return $function_list;
    }

    /**
     * Generate the new line list
     *
     * @return string
     */
    private function _otherList(): string
    {
        $other_list = '';
        foreach ($this->_other_list as $other) {
            $other_list .= $other . "\n";
        }
        return $other_list;
    }

    /**
     * Generate the services.php file
     *
     * @param string $file services.php path
     *
     * @return void
     */
    public function generate(string $file)
    {
        $content = "<?php";
        $content .= "\n\n";
        $content .= $this->_includeList();
        $content .= "\n\n";
        $content .= $this->_functionList();
        $content .= "\n\n";
        $content .= $this->_otherList();
        file_put_contents($file, $content);
    }
}
