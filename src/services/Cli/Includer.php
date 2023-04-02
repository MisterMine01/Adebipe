<?php

namespace Api\Cli;

use stdClass;

class Includer
{

    private function dirRead($path): array
    {
        $file = [];
        $last = [];
        if (is_dir($path)) {
            $path = $this->makeIncludeList($path);
            $file = array_merge($file, $path[0]);
            $last = array_merge($last, $path[1]);
        } else {
            $file[] = $path;
        }
        return [$file, $last];
    }

    private function makeIncludeList($path): array
    {
        $file = [];
        $last = [];
        $dir = scandir($path);
        $make_process = [".", ".."];
        if (in_array('runtime', $dir)) {
            $make_process[] = 'runtime';
            $runtime = fopen($path . '/runtime', 'r');
            $data = fread($runtime, filesize($path . '/runtime'));
            fclose($runtime);
            $runtime_data = explode(PHP_EOL, $data);
            foreach ($runtime_data as $item) {
                if (strpos($item, '@Not ') !== false) {
                    $make_process[] = str_replace('@Not ', '', $item);
                    continue;
                }
                if (strpos($item, '@Last ') !== false) {
                    $last[] = $item;
                    $new_file = $path . str_replace('@Last ', '', $item);
                    $make_process[] = $new_file;
                    $new_file = $this->dirRead($path . "/" . $new_file);
                    $last = array_merge($last, $new_file[0]);
                    $last = array_merge($last, $new_file[1]);
                    continue;
                }
                $make_process[] = $item;
                $new_file = $path . '/' . $item;
                $new_file = $this->dirRead($new_file);
                $file = array_merge($file, $new_file[0]);
                $last = array_merge($last, $new_file[1]);
            }
        }
        foreach ($dir as $item) {
            if (!in_array($item, $make_process)) {
                $new_file = $path . '/' . $item;
                $new_file = $this->dirRead($new_file);
                $file = array_merge($file, $new_file[0]);
                $last = array_merge($last, $new_file[1]);
            }
        }
        return [$file, $last];
    }

    public function setIncludeList($path): array
    {
        $file = $this->makeIncludeList($path);
        $file = array_merge($file[0], $file[1]);
        $file = array_unique($file);
        return $file;
    }

    public function includeList($path): array
    {
        $file = $this->setIncludeList($path);
        $class = [];
        foreach ($file as $item) {
            if (strpos($item, '.php', strlen($item) - 4) !== false) {
                $first = get_declared_classes();
                include_once $item;
                $second = get_declared_classes();
                $class = array_merge($class, array_diff($second, $first));
            }
        }
        return $class;
    }
}
