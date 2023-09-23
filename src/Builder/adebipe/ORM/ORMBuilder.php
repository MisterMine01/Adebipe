<?php

use Adebipe\Services\MsQl;
use Adebipe\Services\ORM;

class ORMBuilder implements BuilderInterface
{
    private $_repositories;

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

    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {
        $orm = $core->getService(ORM::class);
        $core->includeFolder("src/Entity");

        $orm->atStart($core->getService(MsQl::class));
        $this->_repositories = $orm->getRepositories();


        $file_code = file_get_contents(__DIR__ . '/ORM.php');
        $file_code = str_replace("// CODE OF USES GOES HERE", $this->getUsesCode(), $file_code);
        $file_code = str_replace("// MODEL INIT GOES HERE", $this->getInitCode(), $file_code);
        file_put_contents($tmp_file, $file_code);
    }

    public function getUsesCode()
    {
        $uses = array();
        foreach ($this->_repositories as $key => $repository) {
            $uses[] = "use " . $key . ";";
        }
        return implode(PHP_EOL, $uses);
    }

    public function getInitCode()
    {
        $init = array();
        foreach ($this->_repositories as $key => $repository) {
            $init[] = '$this->repository["' . $key . '"] = new Repository("' . $key . '", $this->msql);';
        }
        return implode(PHP_EOL, $init);
    }
}
