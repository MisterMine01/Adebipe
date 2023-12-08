<?php

namespace Adebipe\Builder;

use Adebipe\Model\Model;
use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;
use ReflectionClass;

/**
 * Builder of the ORM
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class ORMBuilder implements BuilderInterface
{
    private array $_repositories;

    /**
     * Return the files to include in the build
     *
     * @return array<string> The files to include in the build
     */
    public function includeFiles(): array
    {
        $files = array();
        foreach ($this->_repositories as $key => $repository) {
            $class_name = $repository->getClassName();
            $reflection = new ReflectionClass($class_name);
            $file = $reflection->getFileName();
            $files[] = $file;
        }
        return $files;
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
        $orm = $core->getService(ORM::class);
        $core->includeFolder("src/Entity");

        $orm->atStart($core->getService(MsQl::class));
        $this->_repositories = $orm->getRepositories();


        $file_code = file_get_contents(__DIR__ . '/ORM.php');
        $file_code = str_replace("// CODE OF USES GOES HERE", $this->_getUsesCode(), $file_code);
        $file_code = str_replace("// MODEL INIT GOES HERE", $this->_getInitCode(), $file_code);
        file_put_contents($tmp_file, $file_code);
    }

    /**
     * Get the code of the uses
     *
     * @return string
     */
    private function _getUsesCode()
    {
        $uses = array();
        foreach ($this->_repositories as $key => $repository) {
            $uses[] = "use " . $key . ";";
        }
        return implode(PHP_EOL, $uses);
    }

    /**
     * Get the code of the models and repositories init
     *
     * @return string
     */
    private function _getInitCode()
    {
        $init = array();
        foreach ($this->_repositories as $key => $repository) {
            if (!is_subclass_of($key, Model::class)) {
                continue;
            }
            $repository = $key::$repository;
            $init[] = '$this->_repository["' . $key . '"] = new ' . $repository . '("' . $key . '", $this->_msql);';
        }
        return implode(PHP_EOL, $init);
    }
}
