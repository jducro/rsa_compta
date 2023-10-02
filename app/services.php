<?php

declare(strict_types=1);

use App\Services\PaypalImportService;
use DI\Container;
use Doctrine\ORM\EntityManager;
use Psr\Container\ContainerInterface;

return function (ContainerInterface $container) {
  $container->set(PaypalImportService::class, static function (Container $c) {
    return new PaypalImportService($c->get(EntityManager::class));
  });
};