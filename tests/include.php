<?php

function includeIn($dir)
{
    foreach (scandir($dir) as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }
        if (is_dir($dir . '/' . $file)) {
            includeIn($dir . '/' . $file);
        } else {
            require_once $dir . '/' . $file;
        }
    }
}

includeIn(__DIR__ . '/abstract');
