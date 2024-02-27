<?php

namespace App\Controller;

use App\Entity\Roulement;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExcelController extends AbstractController
{
    #[Route('/import', name: 'app_import_excel')]
    public function creeruser(Request $request, UserRepository $userRepository, ServiceRepository $serviceRepository, EntityManagerInterface $entityManager): Response
    {
        $spreadsheet = IOFactory::load('assets/excel/planning_test.xlsx');
        $sheet = $spreadsheet->getActiveSheet();

        // =================== DONNEE BRUT EXCEL ====================
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

        // ================== FONCTION VERIFICATION ==================
        function dateRegex($date)
        {
            if (preg_match('/^(0[1-9]|[12][0-9]|3[01])\/(0[1-9]|1[0-2])\/\d{4}$/', $date)) {
                return true;
            }
        }

        function horaireRegex($horaire)
        {
            if (preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9] - (0[0-9]|1[0-9]|2[0-3]):[0-5][0-9]$/', $horaire)) {
                return true;
            }
        }

        function checkAgent($userRepository, $agentId)
        {
            $agentCheck = $userRepository->findOneBy(['username' => $agentId]);
            if($agentCheck) {
                return true;
            }
        }

        //====================== RECUPERATION DONNEE UTILE ==========================
        $formattedData = [];
        $agentId = 0;

        foreach ($data as $row) {
            if ($row != null) {
                switch ($row[0]) {
                    case checkAgent($userRepository, $row[0]):
                        $formattedData['service_' . $agentId] = $row;
                        break;
                    case 'Horaires':
                        $formattedData['horaires_' . $agentId] = $row;
                        $agentId++;
                        break;
                    case (dateRegex($row[0])):
                        $formattedData['date'] = $row;
                        break;
                }
            }
        }

        // ================== FORMATAGE DATE ===========================
        
        $dates = $formattedData['date'];
        $dates = array_map(function ($dateString) {
            return DateTime::createFromFormat('d/m/Y', $dateString);
        }, $dates);

        $formattedData = array_splice($formattedData, 1);

        // ==================== FORMATAGE USER  ============================

        $user_Array = [];
        for ($id = 0; $id < count($formattedData); $id++) {
            $user_Array["user_" . $id] = array_slice($formattedData, 0, 2);
            $formattedData = array_splice($formattedData, 2);
        }


        for($i = 0; $i < count($user_Array); $i++) {

            $user_Array["user_" . $i]['name'] = $user_Array["user_" . $i]['service_' . $i][0];
            $user_Array["user_" . $i]['service_' . $i] = array_splice($user_Array["user_" . $i]['service_' . $i], 1);
            $user_Array["user_" . $i]['horaires_' . $i] = array_splice($user_Array["user_" . $i]['horaires_' . $i], 1);

            $user_Array["user_" . $i]['services'] = $user_Array["user_" . $i]['service_' . $i];
            $user_Array["user_" . $i]['horaires'] = $user_Array["user_" . $i]['horaires_' . $i];

            $user_Array["user_" . $i] = array_splice($user_Array["user_" . $i], 2);
        }


        foreach($user_Array as $i => $user) {

            foreach($user['horaires'] as $keyHoraire => $horaire) {
                if(!horaireRegex($horaire)) {
                    $startTime = DateTime::createFromFormat('H:i', '00:00');
                    $endTime = DateTime::createFromFormat('H:i', '00:00');
                    $horaire = [$startTime, $endTime];
                } else {
                    $heureService = explode(' - ', $horaire);
                    $startTime = DateTime::createFromFormat('H:i', $heureService[0]);
                    $endTime = DateTime::createFromFormat('H:i', $heureService[1]);
                    $horaire = [$startTime, $endTime];

                }
                $user_Array[$i]['horaires'][$keyHoraire] = $horaire;
            }
        }

        // dd($user_Array);
        // ==================== CREATION ROULEMENTS ===================

        $roulement_list = [];
        foreach($user_Array as $user) {

            for($num = 0; $num < count($dates); $num++) {

                $roulement = new Roulement();
                $roulement->setAgent($userRepository->findOneBy(['username' => $user['name']]));
                $roulement->setDate($dates[$num]);
                $roulement->setService($serviceRepository->findOneBy(['label' => $user['services'][$num]]));
                $roulement->setPriseDeService($user['horaires'][$num][0]);
                $roulement->setFinDeService($user['horaires'][$num][1]);

                // $entityManager->persist($roulement);
                // $entityManager->flush();
                $roulement_list[] = $roulement;
            }
        }

        dd($roulement_list);

        return new Response('Données importé avec succès');
    }
}
