<?php

namespace App\Controller;

use App\Repository\FerieRepository;
use App\Repository\RoulementRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RoulementController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function home()
    {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_PLANNING', $this->getUser()->getRoles())) {

            return $this->redirectToRoute('app_roulement_index', [], Response::HTTP_SEE_OTHER);

        } else {

            return $this->redirectToRoute('app_roulement_user', [], Response::HTTP_SEE_OTHER);
        }
    }

    #[Route('/planning', name: 'app_roulement_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, FerieRepository $ferieRepository, RoulementRepository $roulementRepository): Response
    {
        $searchTerm = $request->query->get('search');

        $ferie = $ferieRepository->findAll();

        foreach ($ferie as $day) {

            if($roulementRepository->findByFerie($day->getDate())) {

                $list_ferie = $roulementRepository->findByFerie($day->getDate());

            }
        }

        // dd($list_ferie);

        $data = $userRepository->findUserBySearch($searchTerm);

        return $this->render('roulement/index.html.twig', [
            'users' => $data,
            'searchTerm' => $searchTerm,
            'ferie' => $list_ferie,
        ]);
    }

    #[Route('/personnel', name: 'app_roulement_user', methods: ['GET'])]
    public function userDisplay(RoulementRepository $roulementRepository, Request $request): Response
    {
        // $user = 'SAMY-ARLAYE  RITCHIE JEAN';
        $user = $this->getUser()->getUserIdentifier(); 

        $roulement = $roulementRepository->findByUser($user);

        return $this->render('roulement/user.html.twig', [
            'roulements' => $roulement,
            'user' => $user,
        ]);
    }
}
