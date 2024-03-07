<?php

namespace App\Service;

use App\Entity\Categorie;
use DateTime;
use App\Entity\Service;
use App\Entity\Roulement;
use App\Entity\User;
use App\Repository\CategorieRepository;
use App\Repository\RoulementRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportTxtService
{
    public function __construct(
        private RoulementRepository $roulementRepository,
        private UserRepository $userRepository,
        private ServiceRepository $serviceRepository,
        private CategorieRepository $categorieRepository,
        private EntityManagerInterface $entityManager
    ) {
    }

    public function importTxt()
    {
        // ======================= DONNEE BRUT EXCEL ====================

        $spreadsheet = IOFactory::load(__DIR__.'/../../public/assets/txt/AT240306.TXT');
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

        // ======================= FONCTION ==================

        function convertSecondsToHoursMinutes($seconds)
        {
            // Calcul des heures et des minutes

            $hours = floor($seconds / 60);
            $minutes = $seconds % 60;
            $formattedMinutes = sprintf("%02d", $minutes);

            // Retourner le résultat sous forme de chaîne
            return "$hours:$formattedMinutes";
        }

        function formatHours($hours) {
            
            $hours = str_split($hours);

            return $hours[0].$hours[1] . ":" . $hours[2].$hours[3];
        }

        //===================== RECUPERATION DONNEE PRINCIPALE ==========================
        // dd($data);
        $mainData = [];
        $serviceNumber = 0;
        $depotNumber = 0;
        $depotString = '';

        foreach ($data as $row) {
            if ($row != null) {
                switch ($row[0]) {
                    case (substr($row[0], 0, 1) == "0"):
                        $date = explode("\t", $row[0]);
                        $mainData['date'] = $date[1];
                        break;
                    case (substr($row[0], 0, 1) == "1"):
                        $service = explode("\t", $row[0]);
                        $mainData['services']['service_' . $serviceNumber]['service'] = $service[1];
                        $mainData['services']['service_' . $serviceNumber]['matricule'] = $service[2];
                        $serviceNumber++;
                        break;
                    case (substr($row[0], 0, 1) == "2"):
                        if (isset($row[1])) {
                            $depotString = $row[0] . "\t" . $row[1];
                        } else {
                            $depotString = $row[0];
                        }
                        if(strpos($depotString, "DEPOT")) {
                            $mainData['depots']['depot_' . $depotNumber][] = $depotString;
                            $depotNumber++;
                        }
                        break;
                }
            }
        }

        // =========================== FORMATAGE DATE ===========================

        $date = $mainData['date'];
        $mainData['date'] = DateTime::createFromFormat('d/m/Y', $date);

        // dd($mainData);
        // ============================ FORMATAGE ROTATION ============================

        foreach($mainData['services'] as $key => $services) {

            foreach($mainData['depots'] as $depots) {

                $depotExploded = explode("\t", $depots[0]);
                if($depotExploded[3] == $services['service']) {

                    $mainData['services'][$key]['rotations'][] = $depots[0];

                }
            }
        }

        array_splice($mainData, 2, 1);
        // dd($mainData);

        foreach($mainData['services'] as $key => $service) {
            if(isset($service['rotations'])) {

                foreach($service['rotations'] as $rotation) {

                    $dataExploded = explode("\t", $rotation);
                    $dataExploded =
                    $exploded = [$dataExploded[4] => convertSecondsToHoursMinutes($dataExploded[6]), $dataExploded[5] => convertSecondsToHoursMinutes($dataExploded[7])];

                    $mainData['services'][$key]['rotations'][] = $exploded;
                    if($rotation[0] == "2") {
                        array_splice($mainData['services'][$key]['rotations'], 0, 1);
                    }
                }

            }

        }

        // dd($mainData);
        // =============================================================================== FIN FICHIER ABC ================================================================


        $spreadsheetCRW = IOFactory::load(__DIR__.'/../../public/assets/txt/16122023.CRW');
        $sheetCRW = $spreadsheetCRW->getActiveSheet();

        $dataCRW = [];
        foreach ($sheetCRW->getRowIterator() as $row) {
            $rowData = [];
            foreach ($row->getCellIterator() as $cell) {
                if($cell->getValue() != null) {
                    $rowData[] = $cell->getValue();
                }
            }
            $dataCRW[] = $rowData;
        }

        // dd($dataCRW);
        //===================== RECUPERATION DONNEE PRINCIPALE ==========================
        $mainDataCRW = [];

        foreach ($dataCRW as $row) {
            if ($row != null) {
                
                $fullString = implode(" ", $row);
                $fullStringTrim = str_replace(" ", "", $fullString);

                if(strpos($fullStringTrim, "DEPOT")) {
                    $formatedArray = explode(";", $fullStringTrim);
                    $mainDataCRW[] = $formatedArray;
                    
                }

            }
        }

        // dd($mainDataCRW);

        $rotationId = 0;
        $serviceList = [];
        foreach($mainDataCRW as $key => $value) {

            $serviceList['rotation_' . $rotationId]['service'] = $value[1];
            $serviceList['rotation_' . $rotationId]['priseService']['lieu'] = $value[6];
            $serviceList['rotation_' . $rotationId]['priseService']['heure'] = formatHours($value[7]);
            $serviceList['rotation_' . $rotationId]['sortieDepot']['lieu'] = $value[8];
            $serviceList['rotation_' . $rotationId]['sortieDepot']['heure'] = formatHours($value[9]);
            $serviceList['rotation_' . $rotationId]['rentreDepot']['lieu'] = $value[10];
            $serviceList['rotation_' . $rotationId]['rentreDepot']['heure'] = formatHours($value[11]);
            $serviceList['rotation_' . $rotationId]['finService']['lieu'] = $value[12];
            $serviceList['rotation_' . $rotationId]['finService']['heure'] = formatHours($value[13]);

            $rotationId++;

        }

        // dd($mainData);
        dd($serviceList);

        // ============================ FORMATAGE USER ============================

        $userList = [];
        $sliceID = 0;
        for ($id = 0; $id < count($mainData) / 2; $id++) {
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

                $agent = $this->userRepository->findOneBy(['username' => $user['name']]);
                $currentRoulement = $this->roulementRepository->findOneBy(['agent' => $agent, 'date' => $dates[$num]]);

                if(isset($currentRoulement)) {

                    $this->entityManager->remove($currentRoulement);
                }

                $roulement = new Roulement();
                $roulement->setAgent($agent);
                $roulement->setDate($dates[$num]);
                $roulement->setService($this->serviceRepository->findOneBy(['label' => $user['services'][$num]]) ? $this->serviceRepository->findOneBy(['label' => $user['services'][$num]]) : createService($this->entityManager, $this->serviceRepository, $user['services'][$num]));
                $roulement->setPriseDeService($user['horaires'][$num][0]);
                $roulement->setFinDeService($user['horaires'][$num][1]);

                $this->entityManager->persist($roulement);
                $this->entityManager->flush();

                // $roulementList[] = $roulement;
            }
        }

        // dd($roulementList);
    }

    // ===========================

    public function importCategory()
    {

        $spreadsheet = IOFactory::load(__DIR__.'/../../public/assets/excel/LISTE CDT 2024.xlsx');
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

        function createCategorie($er, $sr, $serviceName)
        {
            $service = new Categorie();
            $service->setLabel($serviceName);

            $er->persist($service);
            $er->flush();

            return $sr->findOneBy(['label' => $serviceName]);
        }

        foreach($data as $user) {
            $userBDD = $this->userRepository->findOneBy(['username' => $user[0]]);
            $matricule = $user[1];

            if($userBDD) {

                $userBDD->setMatricule($matricule);
                if(isset($user[2])) {

                    $userBDD->setCategorie($this->categorieRepository->findOneBy(['label' => $user[2]]) ? $this->categorieRepository->findOneBy(['label' => $user[2]]) : createCategorie($this->entityManager, $this->categorieRepository, $user[2]));
                }

                $this->entityManager->persist($userBDD);
                $this->entityManager->flush();
            }
        }
    }
}
