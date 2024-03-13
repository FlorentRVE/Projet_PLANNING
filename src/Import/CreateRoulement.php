<?php

namespace App\Import;

use App\Repository\RoulementRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class CreateRoulement
{
    private ?array $dataAT = [];
    private ?array $dataCRW = [];
    private ?DateTime $date = null;
    private EntityManagerInterface $entityManager;
    private RoulementRepository $roulementRepository;
    private ServiceRepository $serviceRepository;
    private UserRepository $userRepository;

    public function __construct(
        array $dateAT,
        array $dateCRW,
        DateTime $date,
        EntityManagerInterface $entityManager,
        RoulementRepository $roulementRepository,
        ServiceRepository $serviceRepository,
        UserRepository $userRepository
    ) {
        $this->dataAT = $dateAT;
        $this->dataCRW = $dateCRW;
        $this->date = $date;
        $this->entityManager = $entityManager;
        $this->roulementRepository = $roulementRepository;
        $this->serviceRepository = $serviceRepository;
        $this->userRepository = $userRepository;
    }

    public function createRoulement(): void
    {
        foreach($this->dataAT as $at) {

            foreach($this->dataCRW as $crw) {

                if($at['service'] == $crw['service']) {

                    if($crw['lieuPriseService'] == 'DEPOT') {
                        $priseService = $crw['heurePriseService'];
                    }

                    if($crw['lieuFinService'] == 'DEPOT') {
                        $finService = $crw['heureFinService'];
                    }

                    $nomService = $at['service'];
                    $matricule = $at['matricule'];
                    $nomAgent = $at['nom'];

                    $serv = $this->serviceRepository->findOrCreate($nomService);
                    $agent = $this->userRepository->findOrCreate($matricule, $nomAgent);


                    if(isset($priseService) && isset($finService)) {
                        $roulement = $this->roulementRepository->findOrCreate(
                            $agent,
                            $this->date,
                            $serv,
                            $priseService,
                            $finService
                        );
                    }


                    if(isset($roulement)) {

                        $this->entityManager->persist($roulement);
                        $this->entityManager->flush();
                    }

                }
            }
        }
    }
}
