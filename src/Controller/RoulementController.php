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
        if ($this->getUser()) {
            if (in_array('ROLE_ADMIN', $this->getUser()->getRoles()) || in_array('ROLE_PLANNING', $this->getUser()->getRoles())) {

                return $this->redirectToRoute('app_roulement_index', [], Response::HTTP_SEE_OTHER);

            } else {

                return $this->redirectToRoute('app_roulement_user', [], Response::HTTP_SEE_OTHER);
            }
        } else {

            return $this->redirectToRoute('app_roulement_index', [], Response::HTTP_SEE_OTHER);

        }
    }

    #[Route('/planning', name: 'app_roulement_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, FerieRepository $ferieRepository, RoulementRepository $roulementRepository): Response
    {
        $searchTerm = $request->query->get('search');
        $users = $userRepository->findUserBySearch($searchTerm);

        $ferieDays = $ferieRepository->findAll();
        foreach ($ferieDays as $day) {
            if ($day->getDate()->format('m') == date('m') || $day->getDate()->format('m') == date('m') + 1) {
                $listFerieThisMonthAndNextMonth[] = $day;
            }
        }

        // ============= Récupération et tri des dates =============
        $datesList = array();
        foreach ($users as $user) {
            foreach ($user->getRoulements() as $roulement) {
                if (!in_array($roulement->getDate()->format('d-m-Y'), $datesList)) {
                    $datesList[] = $roulement->getDate()->format('d-m-Y');
                }
            }
        }

        $datesFormated = array();
        foreach ($datesList as $dates) {
            $datesFormated[] = new \DateTime($dates);
        }

        sort($datesFormated);

        return $this->render('roulement/index.html.twig', [
            'users' => $users,
            'searchTerm' => $searchTerm,
            'ferie' => $listFerieThisMonthAndNextMonth,
            'dates' => $datesFormated
        ]);
    }

    #[Route('/ferie', name: 'app_roulement_ferie', methods: ['GET'])]
    public function ferie(Request $request, UserRepository $userRepository, FerieRepository $ferieRepository, RoulementRepository $roulementRepository): Response
    {
        $searchTerm = $request->query->get('search');
        $users = $userRepository->findUserBySearch($searchTerm);
        $ferieDays = $ferieRepository->findAll();

        $listFerie = array();
        foreach ($ferieDays as $day) {
            $roulementFerie = $roulementRepository->findByFerie($day->getDate());
            array_push($listFerie, ...$roulementFerie);
        }

        $listFerieThisMonthAndNextMonth = array();
        foreach ($listFerie as $day) {
            if ($day->getDate()->format('m') == date('m') || $day->getDate()->format('m') == date('m') + 1) {

                $listFerieThisMonthAndNextMonth[] = $day;
            }
        }

        // ============= Récupération et tri des dates =============
        $datesList = array();
        foreach ($listFerieThisMonthAndNextMonth as $roulementferie) {
            if (!in_array($roulementferie->getDate()->format('d-m-Y'), $datesList)) {
                $datesList[] = $roulementferie->getDate()->format('d-m-Y');
            }
        }

        $datesFormated = array();
        foreach ($datesList as $dates) {
            $datesFormated[] = new \DateTime($dates);
        }

        sort($datesFormated);

        return $this->render('roulement/ferie.html.twig', [
            'users' => $users,
            'searchTerm' => $searchTerm,
            'ferie' => $listFerieThisMonthAndNextMonth,
            'dates' => $datesFormated
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
