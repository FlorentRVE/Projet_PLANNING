<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );

            $entityManager->persist($user);
            $entityManager->flush();
            // do anything else you need here, like send an email

            return $this->redirectToRoute('app_roulement_index');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/massRegister', name: 'app_mass_register')]
    public function massRegister(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {

        $spreadsheet = IOFactory::load('assets/excel/Liste_utilisateur.xlsx');
        $sheet = $spreadsheet->getActiveSheet();

        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                if($cell->getValue() != null) {
                    $rowData[] = $cell->getValue();
                }
            }
            $data[] = $rowData;
        }

        $usersList = [];

        for($i = 1; $i < count($data); $i++) {
            if(array_search($data[$i], $data) % 10 == 0) {
                $usersList[] = $data[$i][0];
            };
        }

        foreach($usersList as $userName) {
            $user = new User();
            $user->setUsername($userName);
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    'test'
                )
            );
            $entityManager->persist($user);
            $entityManager->flush();
        }

        return new Response('Utilisateurs crées');
    }
}
