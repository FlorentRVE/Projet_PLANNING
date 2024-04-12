<?php

namespace App\Import;

use App\Repository\RoulementRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use DateTime;

class CreateRoulement
{
    private ?array $data = [];
    private ?DateTime $date = null;
    private EntityManagerInterface $entityManager;
    private RoulementRepository $roulementRepository;
    private ServiceRepository $serviceRepository;
    private UserRepository $userRepository;

    public function __construct(
        array $data,
        DateTime $date,
        EntityManagerInterface $entityManager,
        RoulementRepository $roulementRepository,
        ServiceRepository $serviceRepository,
        UserRepository $userRepository
    ) {
        $this->data = $data;
        $this->date = $date;
        $this->entityManager = $entityManager;
        $this->roulementRepository = $roulementRepository;
        $this->serviceRepository = $serviceRepository;
        $this->userRepository = $userRepository;
    }

    public function createRoulement()
    {
        foreach ($this->data as $key => $dataItem) {

            $matinSoir = $key;
            $service = $this->serviceRepository->findOrCreate($dataItem['service']);
            $agent = $this->userRepository->findOrCreate($dataItem['matricule'], $dataItem['agent']);
            $priseService = $dataItem['priseService'];
            $finService = $dataItem['finService'];

            $priseServiceFormate = DateTime::createFromFormat('H:i', $priseService);
            $finServiceFormate = DateTime::createFromFormat('H:i', $finService);


            $roulement = $this->roulementRepository->findOrCreate(
                $matinSoir,
                $agent,
                $this->date,
                $service,
                $priseServiceFormate,
                $finServiceFormate
            );

            
            if (isset ($roulement)) {
                $this->entityManager->persist($roulement);
                $this->entityManager->flush();
            }
            
        }
    }
}
