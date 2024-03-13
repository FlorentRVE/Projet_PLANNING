<?php

namespace App\Service;

use App\Import\CreateRoulement;
use App\Import\ImportAT;
use App\Import\ImportCRW;
use App\Repository\CategorieRepository;
use App\Repository\RoulementRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;

class ImportTxtService
{
    public function __construct(
        private RoulementRepository $roulementRepository,
        private UserRepository $userRepository,
        private ServiceRepository $serviceRepository,
        private CategorieRepository $categorieRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function importTxt()
    {
        // ============ import fichier ABC =============
        $importAT = new ImportAT(__DIR__.'/../../public/assets/txt/AT240312.TXT', "\t");
        $importAT->import();
        $dataAT = $importAT->getData();
        $date = $importAT->getDate();

        // dd($dataAT);

        // ============= import fichier CRW =============
        $importCRW = new ImportCRW(__DIR__.'/../../public/assets/txt/16122023.CRW', ";");
        $importCRW->import();
        $dataCRW = $importCRW->getData();

        // dd($dataCRW);

        // =========================== CREATION ROULEMENTS ========================

        $createRoulement = new CreateRoulement(
            $dataAT,
            $dataCRW,
            $date,
            $this->entityManager,
            $this->roulementRepository,
            $this->serviceRepository,
            $this->userRepository
        );
        // dd($createRoulement);
        $createRoulement->createRoulement();

    }
}
