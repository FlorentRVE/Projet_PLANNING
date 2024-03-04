<?php

namespace App\Controller;

use App\Entity\Roulement;
use App\Form\RoulementType;
use App\Repository\RoulementRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/roulement')]
class RoulementController extends AbstractController
{
    #[Route('/', name: 'app_roulement_index', methods: ['GET'])]
    public function index(Request $request, UserRepository $userRepository, PaginatorInterface $paginator): Response
    {
        $searchTerm = $request->query->get('search');

        $data = $userRepository->findUserBySearch($searchTerm);

        $data = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('roulement/index.html.twig', [
            'users' => $data,
            'searchTerm' => $searchTerm
        ]);
    }

    #[Route('/userDisplay', name: 'app_roulement_user', methods: ['GET'])]
    public function userDisplay(RoulementRepository $roulementRepository, Request $request): Response
    {
        $searchTerm = $request->query->get('tri');
        $user = 'ANAS KARINE';

        $roulement = $roulementRepository->findByTriAndUser($searchTerm, $user);

        return $this->render('roulement/user.html.twig', [
            'roulements' => $roulement,
        ]);
    }

    #[Route('/new', name: 'app_roulement_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $roulement = new Roulement();
        $form = $this->createForm(RoulementType::class, $roulement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($roulement);
            $entityManager->flush();

            return $this->redirectToRoute('app_roulement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('roulement/new.html.twig', [
            'roulement' => $roulement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_roulement_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Roulement $roulement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RoulementType::class, $roulement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_roulement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('roulement/edit.html.twig', [
            'roulement' => $roulement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_roulement_delete', methods: ['POST'])]
    public function delete(Request $request, Roulement $roulement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$roulement->getId(), $request->request->get('_token'))) {
            $entityManager->remove($roulement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_roulement_index', [], Response::HTTP_SEE_OTHER);
    }
}
