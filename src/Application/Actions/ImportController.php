<?php

namespace App\Application\Actions;

use App\Services\PaypalImportService;
use App\Services\SGImportService;
use App\Services\SogecomImportService;
use Psr\Container\ContainerInterface;

class ImportController
{
    public function __construct(protected ContainerInterface $container)
    {
    }

    public function home($request, $response, $args)
    {
        $this->container->get('view')->render($response, 'imports.html.twig', [
          'name' => 'John Doe'
        ]);
        return $response;
    }

    public function paypal($request, $response, $args)
    {
        $files = $request->getUploadedFiles();

        $csv = $files['importPaypal'];
        if ($csv->getError() === UPLOAD_ERR_OK) {
            if ($handle = fopen($csv->getFilePath(), "r")) {
                $this->container->get(PaypalImportService::class)->import($handle);
            }
        }

        $this->container->get('view')->render($response, 'paypal.html.twig');
        return $response;
    }

    public function sogecom($request, $response, $args)
    {
        $files = $request->getUploadedFiles();

        $csv = $files['importSogecom'];
        if ($csv->getError() === UPLOAD_ERR_OK) {
            if ($handle = fopen($csv->getFilePath(), "r")) {
                $this->container->get(SogecomImportService::class)->import($handle);
            }
        }

        $this->container->get('view')->render($response, 'paypal.html.twig');
        return $response;
    }

    public function sg($request, $response, $args)
    {
        $files = $request->getUploadedFiles();

        $csv = $files['importSG'];
        if ($csv->getError() === UPLOAD_ERR_OK) {
            if ($handle = fopen($csv->getFilePath(), "r")) {
                $this->container->get(SGImportService::class)->import($handle);
            }
        }

        $this->container->get('view')->render($response, 'paypal.html.twig');
        return $response;
    }
}
