<?php

namespace App\Controller;

use App\Repository\FerieRepository;
use App\Repository\RoulementRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/roulement')]
class RoulementController extends AbstractController
{
    #[Route('/', name: 'app_roulement_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, FerieRepository $ferieRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search');

        $ferie = $ferieRepository->findAll();
        $data = $userRepository->findUserBySearch($searchTerm);

        return $this->render('roulement/index.html.twig', [
            'users' => $data,
            'searchTerm' => $searchTerm,
            'ferie' => $ferie,
        ]);
    }

    #[Route('/indiv', name: 'app_roulement_user', methods: ['GET'])]
    public function userDisplay(RoulementRepository $roulementRepository, Request $request): Response
    {
        $searchTerm = $request->query->get('tri');
        $user = 'SAMY-ARLAYE  RITCHIE JEAN'; // ! A remplacer par utilisateur connecté

        $roulement = $roulementRepository->findByTriAndUser($searchTerm, $user);

        return $this->render('roulement/user.html.twig', [
            'roulements' => $roulement,
            'user' => $user,
        ]);
    }
}
