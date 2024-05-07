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

        $arrayOfFilenameAT = array();
        $fileNameCRW = '';

        $filesInFolder = scandir(__DIR__ . '\..\..\public\assets\txt\\');
        foreach ($filesInFolder as $fileName) {
            if (str_contains($fileName, 'AT'))
                array_push($arrayOfFilenameAT, $fileName);

            if (str_contains($fileName, 'CRW'))
                $fileNameCRW = $fileName;
        }

        foreach ($arrayOfFilenameAT as $fileNameAT) {

            // ============ import fichier ABC =============
            $importAT = new ImportAT(__DIR__ . '/../../public/assets/txt/' . $fileNameAT, "\t");
            $importAT->import();
            $dataAT = $importAT->getData();
            $date = $importAT->getDate();

            $dateToday = new \DateTime();
            
            if ($date == $dateToday || $date > $dateToday) {

                // ============= import fichier CRW =============
                $importCRW = new ImportCRW(__DIR__ . '/../../public/assets/txt/' . $fileNameCRW, ";");
                $importCRW->import();
                $dataCRW = $importCRW->getData();

                // ================== FUSION AT et CRW ===============

                $mergeATandCRW = new MergeATandCRW($dataAT, $dataCRW);
                $dataForRoulement = $mergeATandCRW->merge();

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
