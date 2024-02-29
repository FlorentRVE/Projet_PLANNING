<?php

namespace App\Controller;

use App\Entity\Roulement;
use App\Entity\Service;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ExcelController extends AbstractController
{
    #[Route('/import', name: 'app_import_excel')]
    public function importExcel(UserRepository $userRepository, ServiceRepository $serviceRepository, EntityManagerInterface $entityManager): Response
    {
        // ======================= DONNEE BRUT EXCEL ====================

        $spreadsheet = IOFactory::load('assets/excel/ABC - Planning semaine.xls');
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

        // ======================= FONCTION VERIFICATION ==================
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

        //===================== RECUPERATION DONNEE PRINCIPALE ==========================
        $mainData = [];
        $agentId = 0;

        foreach ($data as $row) {
            if ($row != null) {
                switch ($row[0]) {
                    case checkAgent($userRepository, $row[0]):
                        $mainData['services_' . $agentId] = $row;
                        break;
                    case 'Horaires':
                        $mainData['horaires_' . $agentId] = $row;
                        $agentId++;
                        break;
                    case (dateRegex($row[0])):
                        $mainData['date'] = $row;
                        break;
                }
            }
        }

        // =========================== FORMATAGE DATE ===========================

        $dates = $mainData['date'];
        $dates = array_map(function ($dateString) {
            return DateTime::createFromFormat('d/m/Y', $dateString);
        }, $dates);

        $mainData = array_splice($mainData, 1);

        // ============================ FORMATAGE USER ============================

        $userList = [];
        $sliceID = 0;
        for ($id = 0; $id < count($mainData)/2; $id++) {
            $userList["user_" . $id] = array_slice($mainData, $sliceID, 2);
            $sliceID += 2;
        }

        for($i = 0; $i < count($userList); $i++) {

            $userList["user_" . $i]['name'] = $userList["user_" . $i]['services_' . $i][0];
            $userList["user_" . $i]['services'] = array_splice($userList["user_" . $i]['services_' . $i], 1);
            $userList["user_" . $i]['horaires'] = array_splice($userList["user_" . $i]['horaires_' . $i], 1);
            $userList["user_" . $i] = array_splice($userList["user_" . $i], 2);
        }

        foreach($userList as $keyUser => $user) {

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
                $userList[$keyUser]['horaires'][$keyHoraire] = $horaire;
            }
        }

        // =========================== CREATION ROULEMENTS ========================

        function createService($er, $sr, $serviceName)
        {
            $service = new Service();
            $service->setLabel($serviceName);

            $er->persist($service);
            $er->flush();

            return $sr->findOneBy(['label' => $serviceName]);
        }

        // dd($userList);
        // $roulementList = [];
        foreach($userList as $user) {

            for($num = 0; $num < count($user['services']); $num++) {

                $roulement = new Roulement();
                $roulement->setAgent($userRepository->findOneBy(['username' => $user['name']]));
                $roulement->setDate($dates[$num]);
                $roulement->setService($serviceRepository->findOneBy(['label' => $user['services'][$num]]) ? $serviceRepository->findOneBy(['label' => $user['services'][$num]]) : createService($entityManager, $serviceRepository, $user['services'][$num]));
                $roulement->setPriseDeService($user['horaires'][$num][0]);
                $roulement->setFinDeService($user['horaires'][$num][1]);

                // $entityManager->persist($roulement);
                // $entityManager->flush();
                $roulementList[] = $roulement;
            }
        }

        // dd($roulementList);

        return new Response('Données importé avec succès');
    }
}
