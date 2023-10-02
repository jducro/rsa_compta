<?php

define('APP_ROOT', __DIR__);

var_dump(APP_ROOT);

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