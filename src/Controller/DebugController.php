<?php

namespace App\Controller;

use App\Entity\Roulement;
use App\Entity\Service;
use App\Repository\RoulementRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class DebugController extends AbstractController
{
    #[Route('/debug', name: 'app_debug')]
    public function index(RoulementRepository $roulement, UserRepository $user, ServiceRepository $serviceRepository, EntityManagerInterface $em): Response
    {


        dd($roulement->loadAll(new DateTime('2024-03-12')));

        return $this->render('debug/index.html.twig', [
            'controller_name' => 'DebugController',
        ]);
    }
}
