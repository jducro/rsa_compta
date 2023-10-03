<?php

if (!defined('APP_ROOT')) {
  define('APP_ROOT', __DIR__);
}

return [
  'settings' => [
    'slim' => [
      'displayErrorDetails' => true,
      'logErrors' => true,
      'logErrorDetails' => true,
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
  ]
];