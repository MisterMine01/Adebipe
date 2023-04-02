<?php

namespace Api\Cli;

use Api\Services\Interfaces\BuilderServiceInterface;
use ReflectionClass;

include_once __DIR__ . '/Includer.php';
include_once __DIR__ . '/MakeClasses.php';

class Build
{
    function recurse_copy($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }


    public function removeDir($path)
    {
        $files = glob($path . '/*');
        foreach ($files as $file) {
            is_dir($file) ? $this->removeDir($file) : unlink($file);
        }
        rmdir($path);
        return;
    }

    public function setConstructor($service, $all_services_existed): string
    {
        fwrite(STDOUT, print_r($all_services_existed, true));
        $constructor = $service->getConstructor();
        if ($constructor === null) {
            return "$" . $service->getShortName() . " = new " . $service->getName() . "();";
        }
        $params = $constructor->getParameters();
        $params_code = '';
        foreach ($params as $param) {
            $param_class = $param->getClass();
            fwrite(STDOUT, "Param: " . $param->getName() . PHP_EOL);
            fwrite(STDOUT, "Param class: " . $param_class->getName() . PHP_EOL);
            if ($param_class === null) {
                continue;
            }
            $param_class_name = $param_class->getName();
            if (in_array($param_class_name, array_keys($all_services_existed))) {
                $params_code .= $all_services_existed[$param_class_name] . ', ';
            }
        }
        $params_code = substr($params_code, 0, -2);
        return "$" . $service->getShortName() . " = new " . $service->getName() . "($params_code);";
    }

    public function build()
    {
        $getcwd = getcwd();
        $build_dir = $getcwd . '/builddir';
        if (is_dir($build_dir)) {
            // Remove the build folder
            $this->removeDir($build_dir);
        }
        mkdir($build_dir);
        $this->recurse_copy($getcwd . '/public', $build_dir . '/public');

        $includer = new Includer();

        $get_services_classes = $includer->includeList($getcwd . '/services');
        $get_src_classes = $includer->includeList($getcwd . '/src');

        $all_services = MakeClasses::makeClasses(array_merge($get_services_classes, $get_src_classes));

        $container = MakeClasses::$container;

        mkdir($build_dir . '/services');

        $to_include = array();


        foreach ($all_services as $service) {
            if (preg_match('/^Api\\\\Components\\\\/', $service->getName())) {
                continue;
            }
            $file = $service->getFileName();
            fwrite(STDOUT, "Build service: " . $service->getShortName() . PHP_EOL);
            $file_code = file_get_contents($file);
            if ($service->implementsInterface(BuilderServiceInterface::class)) {
                $service_class = $container->getService($service->getName());
                $file_code = $service_class->build($file_code);
                if ($file_code === null) {
                    continue;
                }
            }
            $file_path = $build_dir . '/services/' . $service->getShortName();
            while (file_exists($file_path . ".php")) {
                $file_path = $file_path . "_";
            }
            file_put_contents($file_path . '.php', $file_code);
            $to_include[] = substr($file_path, strlen($build_dir . '/services/')) . '.php';
        }
        file_put_contents($build_dir . '/services/loader.php', "<?php" . PHP_EOL);
        foreach ($to_include as $include) {
            file_put_contents($build_dir . '/services/loader.php', "include_once __DIR__ . '/$include';" . PHP_EOL, FILE_APPEND);
        }

        $all_services_existed = array();
        foreach ($all_services as $service) {
            fwrite(STDOUT, $service->getName() . PHP_EOL);
            if (preg_match('/^Api\\\\Services\\\\/', $service->getName())) {
                $contructor = $this->setConstructor($service, $all_services_existed);
                // Get the name of the variable
                $all_services_existed[$service->getName()] = substr($contructor, 0, strpos($contructor, ' = '));
                file_put_contents($build_dir . '/services/loader.php', $contructor . PHP_EOL, FILE_APPEND);
            }
        }
    }
}
