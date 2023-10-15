<?php

/**
 * Default configuration
 *
 * @author BOUGET Alexandre <abouget68@gmail.com>
 */

return [
    "env_var" => [
        "DB_CONNECTION" => "mysql://webapp:root@mysql:3306/webapp",
        //"VIEW_DIR=src/Views/",
    ],
    "config" => [
        "CORE" => [ // The core configuration
            "DIR" => "src/",
            "LOGGER" => [
                "LOG_IN_FILE" => true,
                "LOG_LEVEL" => 1,
                //"ERROR_CLASS" => "Adebipe\Services\DiscordSentry",
            ],
            "ORM" => [
                "TABLE_MODELS" => "App\Model\TableModel",
            ],
        ],
        "ERROR" => [ // The error configuration
            // TODO
        ],
    ],
];
