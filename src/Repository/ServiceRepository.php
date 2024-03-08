<?php

namespace App\Repository;

use App\Entity\Service;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Service>
 *
 * @method Service|null find($id, $lockMode = null, $lockVersion = null)
 * @method Service|null findOneBy(array $criteria, array $orderBy = null)
 * @method Service[]    findAll()
 * @method Service[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceRepository extends ServiceEntityRepository
{
    private ?array $services = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Service::class);
    }

    public function findOrCreate(string $label)
    {
        if ($this->services === null) {
            $this->services = $this->loadAll();
        }

        // on cree l'entite si elle n'existe pas
        if (!array_key_exists($label, $this->services)) {
            $service = (new Service())->setLabel($label);
            $this->getEntityManager()->persist($service);
            $this->getEntityManager()->flush();

            $this->services[$label] = $service;
        }

        return $this->services[$label];
    }


    /**
     * @return Service[] Returns an array of Service objects
     */
    public function loadAll(): array
    {
        return $this
               ->createQueryBuilder('s', 's.label')
               ->getQuery()
               ->getResult()
        ;
    }
    
    //    /**
    //     * @return Service[] Returns an array of Service objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Service
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
