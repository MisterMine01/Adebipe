<?php

include_once __DIR__ . '/BuilderUtils.php';

class Builder
{

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

    public function build()
    {
        $getcwd = getcwd();
        $build_dir = $getcwd . '/builddir';
        $this->create_buildir($build_dir);
        recurse_copy($getcwd . '/public', $build_dir . '/public');
    }
}
