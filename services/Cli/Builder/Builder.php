<?php

use Adebipe\Cli\Includer;
use Adebipe\Cli\MakeClasses;

require_once __DIR__ . '/BuilderUtils.php';
require_once __DIR__ . '/../Includer.php';
require_once __DIR__ . '/../MakeClasses.php';
require_once __DIR__ . '/Constructor/IncludeList.php';

class Builder
{
    private string $_build_dir;

    public function build()
    {
        $getcwd = getcwd();
        $build_dir = $getcwd . '/builddir';
        $this->_build_dir = $build_dir;
        $this->_createBuildir($build_dir);
        recurse_copy($getcwd . '/public', $build_dir . '/public');

        $includer = new Includer();

        $get_services_classes = $includer->includeList($getcwd . '/services');
        $all_services = MakeClasses::makeClasses($get_services_classes);

        $include_list = new IncludeList();

        $this->_buildInterfaces($include_list);

        $this->_buildServices($include_list, $all_services);

        $include_list->generate($build_dir . '/services.php');
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
            $file = $interface->getFileName();
            $file_code = file_get_contents($file);
            $file_path = $this->_build_dir . '/services/interfaces/' .
                $interface->getShortName() . '.php';
            file_put_contents($file_path, $file_code);
            $include = substr($file_path, strlen($this->_build_dir . '/'));
            $include_list->add($include);
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
        foreach ($all_services as $service) {
            if ($service->isInterface()) {
                continue;
            }
            if ($service->getAttributes("NoBuildable")) {
                continue;
            }
            if ($service->getName() === "NoBuildable") {
                continue;
            }
            if (in_array("Adebipe\\Services\\Interfaces\\CreatorInterface", $service->getInterfaceNames())) {
                continue; // TODO: Build the creator
            }
            if (in_array("Adebipe\\Services\\Interfaces\\BuilderServiceInterface", $service->getInterfaceNames())) {
                continue; // TODO: Build the builder
            }
            $this->_buildOther($include_list, $service);
        }
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
        $file = $service->getFileName();
        $file_code = file_get_contents($file);
        $file_path = $this->_build_dir . '/services/others/' .
            $service->getShortName() . '.php';
        file_put_contents($file_path, $file_code);
        $include = substr($file_path, strlen($this->_build_dir . '/'));
        $include_list->add($include);
    }
}
