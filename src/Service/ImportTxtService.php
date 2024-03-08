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
use PhpOffice\PhpSpreadsheet\Reader\Csv;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

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
        $spreadsheet = $reader->load(__DIR__.'/../../public/assets/txt/AT240306.TXT');
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

        // dd($userList);
        $roulementList = [];

        foreach($mainData as $service) {

            foreach($mainDataCRW as $rotation) {

                if($service['service'] == $rotation['service']) {

                    $agent = $this->userRepository->findOneBy(['matricule' => $service['matricule']]);
                    $serv = $this->serviceRepository->findOrCreate($service['service']);

                    // $agent = $this->userRepository->findOrCreate($service['matricule']);

                    // $currentRoulement = $this->roulementRepository->findOneBy(['agent' => $agent, 'date' => $date]);
                    $currentRoulement = $this->roulementRepository->findOrCreate(['agent' => $service['matricule'], 'date' => $date]);

                    if(isset($currentRoulement)) {

                        $currentRoulement->setAgent($agent);
                        $currentRoulement->setDate($date);
                        $currentRoulement->setService($serv);
                        $currentRoulement->setPriseDeService(DateTime::createFromFormat('H:i', $rotation['heurePriseService']));
                        $currentRoulement->setFinDeService(DateTime::createFromFormat('H:i', $rotation['heureFinService']));

                    } else {

                        $roulement = new Roulement();
                        $roulement->setAgent($agent);
                        $roulement->setDate($date);
                        $roulement->setService($serv);
                        $roulement->setPriseDeService(DateTime::createFromFormat('H:i', $rotation['heurePriseService']));
                        $roulement->setFinDeService(DateTime::createFromFormat('H:i', $rotation['heureFinService']));
                    }


                    // $this->entityManager->persist($roulement);
                    // $this->entityManager->flush();

                    dd($roulement);
                    $roulementList[] = $roulement;
                }
            }
        }

        // dd($roulementList);
    }
}
