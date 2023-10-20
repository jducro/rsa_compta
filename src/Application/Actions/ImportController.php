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
        $this->container->get('view')->render($response, 'imports.html.twig');
        return $response;
    }

    public function paypal($request, $response, $args)
    {
        $files = $request->getUploadedFiles();

        $csv = $files['importPaypal'];
        $error = $this->getUploadError($csv);
        if ($error === '') {
            if ($handle = fopen($csv->getFilePath(), "r")) {
                /** @var PaypalImportService $paypalImportService */
                $paypalImportService = $this->container->get(PaypalImportService::class);
                $paypalImportService->import($handle);
            }
        }

        $this->container->get('view')->render($response, 'imports.html.twig', [
          'error' => $error,
        ]);
        return $response;
    }

    public function sogecom($request, $response, $args)
    {
        $files = $request->getUploadedFiles();

        $csv = $files['importSogecom'];
        $error = $this->getUploadError($csv);
        if ($error === '') {
            if ($handle = fopen($csv->getFilePath(), "r")) {
                /** @var SogecomImportService $sogecomImportService */
                $sogecomImportService = $this->container->get(SogecomImportService::class);
                $sogecomImportService->import($handle);
            }
        }

        $this->container->get('view')->render($response, 'imports.html.twig', [
            'error' => $error,
        ]);
        return $response;
    }

    public function sg($request, $response, $args)
    {
        $files = $request->getUploadedFiles();

        $csv = $files['importSG'];
        $error = $this->getUploadError($csv);
        if ($error === '') {
            if ($handle = fopen($csv->getFilePath(), "r")) {
                /** @var SGImportService $sgImportService */
                $sgImportService = $this->container->get(SGImportService::class);
                $sgImportService->import($handle);
            }
        }

        $this->container->get('view')->render($response, 'imports.html.twig', [
            'error' => $error,
        ]);
        return $response;
    }

    protected function getUploadError($csv): string
    {
        return match ($csv->getError()) {
            UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été envoyé',
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Le fichier envoyé dépasse la taille maximale autorisée',
            UPLOAD_ERR_PARTIAL => 'Le fichier n\'a été que partiellement envoyé',
            UPLOAD_ERR_NO_TMP_DIR => 'Erreur système : aucun répertoire temporaire',
            UPLOAD_ERR_CANT_WRITE => 'Erreur système : impossible d\'écrire sur le disque',
            UPLOAD_ERR_EXTENSION => 'Erreur système : une extension PHP a arrêté l\'envoi de fichier',
            UPLOAD_ERR_OK => '',
            default => 'Erreur lors de l\'import du fichier',
        };
    }
}
