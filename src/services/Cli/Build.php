<?php

namespace Adebipe\Cli;

include_once __DIR__ . '/Includer.php';
include_once __DIR__ . '/MakeClasses.php';
include_once __DIR__ . '/BuildFunc/BuildInterfaces.php';
include_once __DIR__ . '/BuildFunc/BuildServices.php';
include_once __DIR__ . '/BuildFunc/BuildLoader.php';

/**
 * Recursively copy files from one directory to another
 * @param string $src
 * @param string $dst
 * @return void
 */
function recurse_copy($src, $dst): void
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurse_copy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}

/**
 * Recursively remove a directory
 * @param string $path
 */
function removeDir(string $path): void
{
    $files = glob($path . '/*');
    foreach ($files as $file) {
        is_dir($file) ? removeDir($file) : unlink($file);
    }
    rmdir($path);
    return;
}

/**
 * Build the project
 */
class Build
{
    /**
     * Build the project
     */
    public function build()
    {
        $getcwd = getcwd();
        $build_dir = $getcwd . '/builddir';
        if (is_dir($build_dir)) {
            // Remove the build folder
            removeDir($build_dir);
        }
        mkdir($build_dir);
        mkdir($build_dir . '/services');
        mkdir($build_dir . '/services/interfaces');

        recurse_copy($getcwd . '/public', $build_dir . '/public');
        copy(__DIR__ . "/build/router", $build_dir . '/router');

        $includer = new Includer();

        $get_services_classes = $includer->includeList($getcwd . '/services');
        $get_src_classes = $includer->includeList($getcwd . '/src');

        $all_services = MakeClasses::makeClasses(array_merge($get_services_classes, $get_src_classes));


        $to_include = build_interfaces($build_dir . '/services');

        $to_include = array_merge($to_include, build_services($build_dir . '/services', $all_services));

        file_put_contents($build_dir . '/services/loader.php', "<?php" . PHP_EOL);
        foreach ($to_include as $include) {
            file_put_contents($build_dir . '/services/loader.php', "include_once __DIR__ . '/$include';" . PHP_EOL, FILE_APPEND);
        }
        continue_loader($build_dir . '/services/loader.php', $all_services);
    }
}
