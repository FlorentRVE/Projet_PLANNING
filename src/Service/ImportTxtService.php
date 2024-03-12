<?php

namespace App\Service;

use DateTime;
use App\Repository\CategorieRepository;
use App\Repository\RoulementRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

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

        $reader = new Csv();
        $reader->setDelimiter("\t");
        $spreadsheet = $reader->load(__DIR__.'/../../public/assets/txt/AT240312.TXT');
        $data = $spreadsheet->getActiveSheet()->toArray();


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
        //===================== RECUPERATION DONNEE PRINCIPALE ==========================
        // dd($data);
        $mainData = [];
        foreach ($data as $row) {
            if ($row != null) {
                switch ($row[0]) {
                    case ("0"):
                        $date = DateTime::createFromFormat('d/m/Y', $row[1]);
                        break;
                    case ("1"):
                        $service = [
                            "service" => $row[1],
                            "matricule" => $row[2]
                        ];
                        $mainData[] = $service;
                        break;
                    case ("2"):
                        if ($row[4] == 'DEPOT' || $row[5] == 'DEPOT') {

                            $depot = [
                                "service" => $row[3], // pour vérif
                                "lieu_sortie" => $row[4],
                                "heure_sortie" => convertSecondsToHoursMinutes($row[6]),
                                "lieu_rentree" => $row[5],
                                "heure_rentree" => convertSecondsToHoursMinutes($row[7])
                            ];

                            foreach($mainData as $key => $services) {
                                if($services['service'] == $row[3]) {
                                    $mainData[$key]['depots'][] = $depot;
                                }
                            }
                        }
                        break;
                    case("4"):
                        foreach($mainData as $key => $services) {
                            if($services['matricule'] == $row[1]) {
                                $mainData[$key]['nom'] = $row[2];
                            }
                        }

                }
            }
        }

        // dd($mainData);
        // =============================================================================== FICHIER CRW ================================================================

        $reader = new Csv();
        $reader->setDelimiter(';');
        $spreadsheet = $reader->load(__DIR__.'/../../public/assets/txt/16122023.CRW');
        $dataCRW = $spreadsheet->getActiveSheet()->toArray();

        // dd($dataCRW);
        //===================== RECUPERATION DONNEE PRINCIPALE ==========================
        $mainDataCRW = [];

        foreach ($dataCRW as $row) {

            if(trim($row[6]) == "DEPOT" || trim($row[8]) == "DEPOT" || trim($row[10]) == "DEPOT" || trim($row[12]) == "DEPOT") {

                $depot = [
                    "service" => trim($row[1]),
                    "lieuPriseService" => trim($row[6]),
                    "heurePriseService" => trim($row[7]),
                    "lieuSortieDepot" => trim($row[8]),
                    "heureSortieDepot" => trim($row[9]),
                    "lieuRetourDepot" => trim($row[10]),
                    "heureRetourDepot" => trim($row[11]),
                    "lieuFinService" => trim($row[12]),
                    "heureFinService" => trim($row[13])
                ];

                $mainDataCRW[] = $depot;
            }
        }

        // dd($mainData);
        // dd($mainDataCRW);
        // =========================== CREATION ROULEMENTS ========================

        $roulementList = [];

        foreach($mainData as $service) {

            foreach($mainDataCRW as $rotation) {

                if($service['service'] == $rotation['service']) {


                    if($rotation['lieuPriseService'] == 'DEPOT') {
                        $priseService = $rotation['heurePriseService'];
                    }

                    if($rotation['lieuFinService'] == 'DEPOT') {
                        $finService = $rotation['heureFinService'];
                    }

                    $nomService = $service['service'];
                    $matricule = $service['matricule'];
                    $nomAgent = $service['nom'];

                    $serv = $this->serviceRepository->findOrCreate($nomService);
                    $agent = $this->userRepository->findOrCreate($matricule, $nomAgent);

                    if(isset($priseService) && isset($finService)) {

                        $roulement = $this->roulementRepository->findOrCreate($agent, $date, $serv, $priseService, $finService);
                    }


                    if(isset($roulement)) {

                        $this->entityManager->persist($roulement);
                        $this->entityManager->flush();
                    }

                    // if(isset($roulement)) $roulementList[] = $roulement;
                }
            }
        }

        // dd($roulementList);
    }
}
