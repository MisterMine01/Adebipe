<?php

namespace Adebipe\Builder;

use Adebipe\Services\ConfigRunner;
use Adebipe\Services\Settings;

/**
 * The builder to create the ConfigRunner class
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */
class RunnerBuilder implements BuilderInterface
{
    /**
     * Return the files to include in the build
     *
     * @return array<string> The files to include in the build
     */
    public function includeFiles(): array
    {
        return [];
    }

    /**
     * Build the classes and write them in the temporary file
     *
     * @param string               $tmp_file The temporary file to write the classes
     * @param CoreBuilderInterface $core     The core builder
     *
     * @return void
     */
    public function build(string $tmp_file, CoreBuilderInterface $core): void
    {
        $configRunner = $core->getService(ConfigRunner::class);
        $env = var_export($configRunner->getEnv(), true);
        $file_code = file_get_contents(__DIR__ . '/ConfigRunner.php');
        $file_code = str_replace('["HERE GOES ENV"]', $env, $file_code);
        $config = var_export(Settings::getConfig(null), true);
        $file_code = str_replace('["HERE GOES CONFIG"]', $config, $file_code);
        file_put_contents($tmp_file, $file_code);
    }
}
