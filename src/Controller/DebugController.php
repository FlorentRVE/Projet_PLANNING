<?php

namespace App\Controller;

use App\Helper\FormatNameHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DebugController extends AbstractController
{
    #[Route('/debug', name: 'app_debug')]
    public function index(): Response {

        $name = "AH-YONE  CHRISTINE                   iu";

        $formattedName = FormatNameHelper::formatName($name);

        dd($formattedName);

        return $this->render('debug/index.html.twig', [
            'controller_name' => 'DebugController',
        ]);
    }
}
