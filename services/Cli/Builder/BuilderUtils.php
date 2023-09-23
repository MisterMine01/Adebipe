<?php

/**
 * Recursively copy files from one directory to another
 *
 * @param  string $src
 * @param  string $dst
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
 *
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
