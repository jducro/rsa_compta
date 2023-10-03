<?php

declare(strict_types=1);

use App\Application\Settings\Settings;
use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Logger;

if (!defined('APP_ROOT')) {
    define('APP_ROOT', dirname(__DIR__));
}

return function (ContainerBuilder $containerBuilder) {

    // Global Settings Object
    $containerBuilder->addDefinitions([
        SettingsInterface::class => function () {
            return new Settings([
                'displayErrorDetails' => true, // Should be set to false in production
                'logError'            => false,
                'logErrorDetails'     => false,
                'logger' => [
                    'name' => 'slim-app',
                    'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../logs/app.log',
                    'level' => Logger::DEBUG,
                ],
                'doctrine' => [
                    'dev_mode' => true,
                    'cache_dir' => APP_ROOT . '/var/doctrine',
                    'metadata_dirs' => [APP_ROOT . '/src/Domain'],
                    'connection' => [
                        'driver' => 'pdo_sqlite',
                        'path' => APP_ROOT . '/var/db.sqlite',
                    ],
                ]
            ]);
        }
    ]);
};
