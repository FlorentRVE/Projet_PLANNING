<?php

namespace App\Controller;

use App\Service\ImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    public function __construct(
        private ImportService $importService,
    ) {
    }

    #[Route('/import', name: 'app_import_txt')]
    public function importData(): Response
    {
        $this->importService->createRoulementFromImport();

        return new Response('<h1>Données mis à jour avec succès ✅<h1>');
    }

}
