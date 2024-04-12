<?php

namespace App\Service;

use App\Import\ImportAT;
use App\Import\ImportCRW;
use App\Import\MergeATandCRW;
use App\Import\CreateRoulement;
use App\Repository\CategorieRepository;
use App\Repository\RoulementRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use DirectoryIterator;
use Doctrine\ORM\EntityManagerInterface;

class ImportService
{
    public function __construct(
        private RoulementRepository $roulementRepository,
        private UserRepository $userRepository,
        private ServiceRepository $serviceRepository,
        private CategorieRepository $categorieRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function createRoulementFromImport()
    {

        $arrayOfFilename = array();

        $filesInFolder = scandir(__DIR__ . '\..\..\public\assets\txt\\');
        foreach ($filesInFolder as $fileName) {
            if (str_contains($fileName, 'AT'))
                array_push($arrayOfFilename, $fileName);
        }

        foreach ($arrayOfFilename as $fileName) {

            // ============ import fichier ABC =============
            $importAT = new ImportAT(__DIR__ . '/../../public/assets/txt/' . $fileName, "\t");
            $importAT->import();
            $dataAT = $importAT->getData();
            $date = $importAT->getDate();

            $dateToday = new \DateTime();
            
            if ($date == $dateToday || $date > $dateToday) {

                // !dd($dataAT);

                // ============= import fichier CRW =============
                $importCRW = new ImportCRW(__DIR__ . '/../../public/assets/txt/16122023.CRW', ";");
                $importCRW->import();
                $dataCRW = $importCRW->getData();

                // !dd($dataCRW);

                // ================== FUSION AT et CRW ===============

                $mergeATandCRW = new MergeATandCRW($dataAT, $dataCRW);
                $dataForRoulement = $mergeATandCRW->merge();

                // !dd($dataForRoulement);

                // =========================== CREATION ROULEMENTS ========================

                $createRoulement = new CreateRoulement(
                    $dataForRoulement,
                    $date,
                    $this->entityManager,
                    $this->roulementRepository,
                    $this->serviceRepository,
                    $this->userRepository
                );

                $createRoulement->createRoulement();
            }
        }

    }
}
