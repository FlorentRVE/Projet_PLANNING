<?php

namespace App\Controller;

use App\Service\ImportTxtService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExcelController extends AbstractController
{    
    public function __construct(
        private ImportTxtService $importTxt,
        )
    {
    }

    #[Route('/import', name: 'app_import_txt')]
    public function importTxt(): Response
    {
        $this->importTxt->importTxt();

        return new Response('|| Données importé avec succès ! ||');
    }
 
}
