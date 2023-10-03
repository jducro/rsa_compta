<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\Line\DbLineRepository;
use App\Services\ExcelExportService;
use App\Services\PaypalImportService;
use App\Services\SGImportService;
use App\Services\SogecomImportService;
use DI\Container;
use Doctrine\ORM\EntityManager;

return function (Container $container) {
  $container->set(PaypalImportService::class, static function (Container $c) {
    return new PaypalImportService($c->get(EntityManager::class));
  });
  $container->set(SogecomImportService::class, static function (Container $c) {
    return new SogecomImportService($c->get(EntityManager::class));
  });
  $container->set(SGImportService::class, static function (Container $c) {
    return new SGImportService($c->get(EntityManager::class));
  });
  $container->set(ExcelExportService::class, static function (Container $c) {
    return new ExcelExportService($c->get(DbLineRepository::class));
  });
};