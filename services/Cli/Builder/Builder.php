<?php

use Adebipe\Cli\Includer;
use Adebipe\Cli\MakeClasses;

require_once __DIR__ . '/BuilderUtils.php';
require_once __DIR__ . '/../Includer.php';
require_once __DIR__ . '/../MakeClasses.php';
require_once __DIR__ . '/IncludeList.php';

class Builder
{

    

    public function build()
    {
        $getcwd = getcwd();
        $build_dir = $getcwd . '/builddir';
        $this->create_buildir($build_dir);
        recurse_copy($getcwd . '/public', $build_dir . '/public');

        $includer = new Includer();

        $get_services_classes = $includer->includeList($getcwd . '/services');
        $all_services = MakeClasses::makeClasses($get_services_classes);

        $include_list = IncludeList();

        $this->
    }

    private function create_buildir(string $build_dir)
    {

        if (is_dir($build_dir)) {
            // Remove the build folder
            removeDir($build_dir);
        }
        mkdir($build_dir);
        mkdir($build_dir . '/services');
        mkdir($build_dir . '/services/interfaces');
    }

    private function build_interfaces(IncludeList $include_list, array $all_services): 
}
