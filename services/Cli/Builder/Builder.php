<?php

use Adebipe\Cli\Includer;
use Adebipe\Cli\MakeClasses;
use Adebipe\Services\Container;
use Adebipe\Services\Injector;
use Adebipe\Services\Interfaces\BuilderServiceInterface;
use Adebipe\Services\Interfaces\CreatorInterface;

require_once __DIR__ . '/BuilderUtils.php';
require_once __DIR__ . '/../Includer/IncluderInterface.php';
require_once __DIR__ . '/../Includer/Includer.php';
require_once __DIR__ . '/../MakeClasses.php';
require_once __DIR__ . '/Constructor/IncludeList.php';
require_once __DIR__ . '/Constructor/ServicesBuilder.php';
require_once __DIR__ . '/BuilderHelper.php';

class Builder
{
    private string $_build_dir;

    private Includer $_includer;

    private BuilderHelper $_builder_helper;

    public function __construct()
    {
        $this->_includer = new Includer();
    }

    public function build()
    {
        $getcwd = getcwd();
        $build_dir = $getcwd . '/builddir';
        $this->_build_dir = $build_dir;
        $this->_createBuildir($build_dir);
        recurse_copy($getcwd . '/public', $build_dir . '/public');

        $get_services_classes = $this->_includer->includeAllFile($getcwd . '/services');
        $all_services = MakeClasses::makeClasses($get_services_classes);


        $this->_builder_helper = new BuilderHelper($this->_includer, MakeClasses::$container);

        $include_list = new IncludeList();

        $this->_buildInterfaces($include_list);

        $this->_buildServices($include_list, $all_services);

        $include_list->generate($build_dir . '/services.php');

        removeDir($build_dir . '/tmp');
    }

    /**
     * Create the build directory
     * 
     * @return void
     */
    private function _createBuildir()
    {

        if (is_dir($this->_build_dir)) {
            // Remove the build folder
            removeDir($this->_build_dir);
        }
        mkdir($this->_build_dir);
        mkdir($this->_build_dir . '/services');
        mkdir($this->_build_dir . '/services/interfaces');
        mkdir($this->_build_dir . '/services/others');
        mkdir($this->_build_dir . '/includes');
        mkdir($this->_build_dir . '/tmp');
    }

    /**
     * Build All interfaces on Adebipe namespace
     * 
     * @param IncludeList $include_list The list of the interfaces
     * 
     * @return void
     */
    private function _buildInterfaces(IncludeList $include_list): void
    {
        foreach (get_declared_interfaces() as $interface) {
            $interface = new ReflectionClass($interface);
            if (!preg_match('/^Adebipe\\\\/', $interface->getName())) {
                continue;
            }
            if ($interface->getAttributes(NoBuildable::class)) {
                continue;
            }
            $this->_buildFile($include_list, $interface, '/services/interfaces/');
        }
    }

    /**
     * Build all services
     * 
     * @param IncludeList            $include_list The list of the interfaces
     * @param array<ReflectionClass> $all_services The list of all services
     * 
     * @return void
     */
    private function _buildServices(IncludeList $include_list, array $all_services): void
    {
        $contained_services = [];
        foreach ($all_services as $service) {
            if ($service->isInterface()) {
                continue;
            }
            if ($service->getAttributes(NoBuildable::class)) {
                continue;
            }
            if ($service->getName() === NoBuildable::class) {
                continue;
            }
            if (Container::class === $service->getName()) {
                continue;
            }
            if (Injector::class === $service->getName()) {
                continue;
            }
            if (in_array(BuilderServiceInterface::class, $service->getInterfaceNames())) {
                $service = $this->_buildBuilder($include_list, $service);
            }
            if (in_array(CreatorInterface::class, $service->getInterfaceNames())) {
                $this->_buildCreator($include_list, $service);
                continue;
            }
            $this->_buildOther($include_list, $service);
        }
    }

    private function _buildBuilder(IncludeList $include_list, ReflectionClass $service): ReflectionClass
    {
        $container = MakeClasses::$container;
        $class = $container->getService($service->getName());

        $builder_file = $service->getFileName();
        $builder_name = $class->build();
        try {
            $builder_class_name = $this->_includer->includeFile("src/Builder/" . $builder_name . ".php")[0];
        } catch (Throwable $e) {
            return $service;
        }
        $builder_class = new ReflectionClass($builder_class_name);

        $tmp = $this->_build_dir . '/tmp/' . uniqid() . '.php';
        
        $instance = $builder_class->newInstance();

        $instance->build($tmp, $this->_builder_helper);

        $all_included = $instance->includeFiles();

        foreach ($all_included as $file) {
            $filename = explode('/', $file);
            $filename = $filename[count($filename) - 1];
            $new_file = $this->_build_dir . '/services/' . $filename;
            file_put_contents($new_file, file_get_contents($file));
            $include = substr($new_file, strlen($this->_build_dir . '/'));
            $include_list->add($include);
        }

        $result = $this->_includer->includeFile($tmp);
        $result = $result[0];
        $result = new ReflectionClass($result);
        return $result;
    }

    private function _buildCreator(IncludeList $include_list, ReflectionClass $service): void
    {
        $this->_buildFile($include_list, $service, '/services/');
        $service_builder = new ServicesBuilder($service);
        $include_list->addFunction($service_builder->generate_function_constructor());
    }

    /**
     * Build all other services
     * 
     * @param IncludeList     $include_list The list of the interfaces
     * @param ReflectionClass $all_services The list of all services
     * 
     * @return void
     */
    private function _buildOther(IncludeList $include_list, ReflectionClass $service): void
    {
        $this->_buildFile($include_list, $service, '/services/others/');
    }


    private function _buildFile(IncludeList $include_list, ReflectionClass $service, string $directory)
    {
        $file = $service->getFileName();
        $file_code = file_get_contents($file);
        $file_path = $this->_build_dir . $directory . $service->getShortName() . '.php';
        if (preg_match_all("/namespace (.*)\\\\Generated;/", $file_code, $matches)) {
            $file_code = str_replace(
                "namespace " . $service->getNamespaceName() . ";",
                "namespace " . $matches[1][0] . ";",
                $file_code
            );
        }
        file_put_contents($file_path, $file_code);
        $include = substr($file_path, strlen($this->_build_dir . '/'));
        $include_list->add($include);
    }
}
