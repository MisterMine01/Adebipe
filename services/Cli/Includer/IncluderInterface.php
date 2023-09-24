<?php

namespace Adebipe\Cli\Includer;

/**
 * Interface for the includer
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
interface IncluderInterface
{
    /**
     * Find all files in a directory and his subdirectories
     *
     * @param string $path The path of the directory
     *
     * @return array<string>
     */
    public function findAllFile($path): array;

    /**
     * Include all files in a directory and his subdirectories
     *
     * @param string $path The path of the directory
     *
     * @return array<string>
     */
    public function includeAllFile($path): array;

    /**
     * Include a file
     *
     * @param string $path The path of the file
     *
     * @return array<string> The classes declared in the file
     */
    public function includeFile($path): array;
}
