<?php

namespace App\Controller;

use App\Entity\Roulement;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExcelController extends AbstractController
{

    #[Route('/import', name: 'app_import_excel')]
    public function creeruser(Request $request)
    {
        $spreadsheet = IOFactory::load('assets/excel/planning_test.xlsx');
        $sheet = $spreadsheet->getActiveSheet();

        // =========== Récupérer les données du tableau Excel =============
        $data = [];
        foreach ($sheet->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                if($cell->getValue() != null){
                    $rowData[] = $cell->getValue();                    
                }
            }
            $data[] = $rowData;
        }

        $date =$data[9];

        // isolé nombre terminant par "1"
        // premier element du tableau = agent reste = service

        dd($data);

        // ================= Formater les données dans le format souhaité =============
        // $formattedData = [];
        // foreach ($data as $row) {
        //     $formattedData[] = [
        //         'date' => $row[7],
        //     ];
        // }

        // dd($formattedData);

        // $newFormattedData = [];
        // foreach ($formattedData as $user) {
        //     $username = $user['username'];
        //     $modifiedUsername = strtoupper($username);
        //     $modifiedUsername = str_replace(['é', 'è', 'ê', 'ë'], 'E', $modifiedUsername);
        //     $user['username'] = $modifiedUsername;
        //     $newFormattedData[] = $user;
        // }

        // ============ PARCOURIR TOUTES LES LIGNES DU TABLEAU ET CREE USER =================

        // foreach ($newFormattedData as $usera) {
        //     $roulement = new Roulement();
            // $user->setUsername($usera['username']);
            // $user->setRoles(['ROLE_ACTIF']);

            // $user->setPassword(
            //     $userPasswordHasher->hashPassword(
            //         $user,
            //         $usera['matricule']
            //     )
            // );

            // $entityManager->persist($user);
            // $entityManager->flush();
        // }

        return new Response('Données importé avec succès');
    }
}
