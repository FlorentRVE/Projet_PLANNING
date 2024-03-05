<?php

namespace App\Controller;

use App\Service\ImportExcelService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExcelController extends AbstractController
{    
    public function __construct(
        private ImportExcelService $importExcel
        )
    {
    }

    // #[Route('/import', name: 'app_import_excel')]
    // public function importExcel(): Response
    // {
    //     $this->importExcel->importExcel();

    //     return new Response('Données importé avec succès');
    // }

    // #[Route('/import', name: 'app_import_excel')]
    // public function importExcel(): Response
    // {
    //     $this->importExcel->importCategory();

    //     return new Response('Données importé avec succès');
    // }
}
