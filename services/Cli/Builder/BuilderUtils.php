<?php

namespace Adebipe\Cli\Builder;

/**
 * Recursively copy files from one directory to another
 *
 * @param string $src Source of files being moved
 * @param string $dst Destination of files being moved
 *
 * @return void
 */
function recurseCopy($src, $dst): void
{
    $dir = opendir($src);
    @mkdir($dst);
    while (false !== ($file = readdir($dir))) {
        if (($file != '.') && ($file != '..')) {
            if (is_dir($src . '/' . $file)) {
                recurseCopy($src . '/' . $file, $dst . '/' . $file);
            } else {
                copy($src . '/' . $file, $dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}


/**
 * Recursively remove a directory
 *
 * @param string $path Path of the directory to remove
 *
 * @return void
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
