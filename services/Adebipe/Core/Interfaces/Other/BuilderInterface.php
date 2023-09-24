<?php

namespace Adebipe\Builder;

/**
 * Interface of the builder classes
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
#[NoBuildable]
interface BuilderInterface
{
    /**
     * Return the files to include in the build
     *
     * @return array<string> The files to include in the build
     */
    public function includeFiles(): array;

    /**
     * Build the classes and write them in the temporary file
     *
     * @param string               $tmp_file The temporary file to write the classes
     * @param CoreBuilderInterface $core     The core builder
     *
     * @return void
     */
    public function build(string $tmp_file, CoreBuilderInterface $core): void;
}
