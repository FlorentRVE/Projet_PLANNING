<?php

namespace App\Controller;

use App\Entity\Roulement;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use DateTime;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExcelController extends AbstractController
{
    #[Route('/import', name: 'app_import_excel')]
    public function creeruser(Request $request, UserRepository $userRepository, ServiceRepository $serviceRepository): Response
    {
        $spreadsheet = IOFactory::load('assets/excel/planning_test.xlsx');
        $sheet = $spreadsheet->getActiveSheet();

        // =========== Récupérer les données du tableau Excel =============
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

        // ==== recupere nom agent dans entité =========
        $agent_list = $userRepository->findAll();
        $agent_name = [];
        foreach ($agent_list as $agent) {
            $agent_name[] = $agent->getUsername();
        }

        // ==== REGEX =====
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

        //=================
        $formattedData = [];
        $horaireId = 0;
        $agentId = 0;

        foreach ($data as $row) {
            if ($row != null) {
                switch ($row[0]) {
                    case 'Horaires':
                        $formattedData[] = [
                            'horaires_' . $horaireId => $row,
                        ];
                        $horaireId++;
                        break;
                    case in_array($row[0], $agent_name):
                        $formattedData[] = [
                            'service_' . $agentId => $row,
                        ];
                        $agentId++;
                        break;
                    case (dateRegex($row[0])):
                        $formattedData[] = [
                            'date' => $row,
                        ];
                        break;


                }
            }

        }

        // ============ DATE =============
        $dates = $formattedData[0]['date'];
        $dates = array_map(function ($dateString) {
            return DateTime::createFromFormat('d/m/Y', $dateString);
        }, $dates);

        $formattedData = array_splice($formattedData, 1);

        // ==================== USER ARRAY ============================

        $user_Array = [];
        for ($id = 0; $id < count($formattedData); $id++) {

            $user_Array["user_" . $id] = array_slice($formattedData, 0, 2);
            $formattedData = array_splice($formattedData, 2);
        }

        for($i = 0; $i < count($user_Array); $i++) {

            $user_Array["user_" . $i]['name'] = $user_Array["user_" . $i][0]['service_' . $i][0];
            $user_Array["user_" . $i][0]['service_' . $i] = array_splice($user_Array["user_" . $i][0]['service_' . $i], 1);
            $user_Array["user_" . $i][1]['horaires_' . $i] = array_splice($user_Array["user_" . $i][1]['horaires_' . $i], 1);

            $user_Array["user_" . $i]['services'] = $user_Array["user_" . $i][0]['service_' . $i];
            $user_Array["user_" . $i]['horaires'] = $user_Array["user_" . $i][1]['horaires_' . $i];

            $user_Array["user_" . $i] = array_splice($user_Array["user_" . $i], 2);
        }

        foreach($user_Array as $user) {

            $user['horaires'] = array_map(function ($timeRange) {
                $times = explode(" - ", $timeRange);
                if (count($times) === 2) {
                    $startTime = DateTime::createFromFormat('H:i', $times[0]);
                    $endTime = DateTime::createFromFormat('H:i', $times[1]);
                    return [$startTime, $endTime];
                }
                return DateTime::createFromFormat('H:i', '00:00'); // ou une autre valeur par défaut pour les plages horaires incorrectes
            }, $user['horaires']);

        }

        dd($user_Array);

        // ============ PARCOURIR TOUTES LES LIGNES DU TABLEAU ET CREE USER =================

        foreach($dates as $date) {

            for($num = 0; $num < count($user_Array); $num++) {

                $roulement = new Roulement();
                $roulement->setDate($date);
                $roulement->setAgent($userRepository->findOneBy(['username' => $user_Array['user_' . $num][0]['service_' . $num][0]]));
                $roulement->setService($serviceRepository->findOneBy(['label' => $user_Array['user_' . $num][0]['service_' . $num][$num]]));
                $roulement->setPriseDeService(DateTime::createFromFormat('H:i', '08:00'));
                $roulement->setFinDeService(DateTime::createFromFormat('H:i', '17:00'));
            }
        }

        // dd($roulement);

        // $entityManager->persist($user);
        // $entityManager->flush();
        // }

        return new Response('Données importé avec succès');
    }
}
