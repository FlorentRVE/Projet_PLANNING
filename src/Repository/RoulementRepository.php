<?php

namespace App\Repository;

use App\Entity\Roulement;
use App\Entity\Service;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Roulement>
 *
 * @method Roulement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Roulement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Roulement[]    findAll()
 * @method Roulement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoulementRepository extends ServiceEntityRepository
{
    private ?array $roulements = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Roulement::class);
    }

    public function findByUser($user)
    {
        return $this->createQueryBuilder('r')
            ->select('a, r')
            ->innerJoin('r.agent', 'a')
            ->andWhere('a.username = :u')
            ->setParameter('u', $user)
            ->orderBy('r.date', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findByFerie($value): ?array
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.date = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findOrCreate(string $matinSoir, User $agent, DateTime $date, Service $service, DateTime $priseService, DateTime $finService)
    {
        $this->roulements = $this->loadAll($date);

        if (!array_key_exists($matinSoir, $this->roulements)) {

            $roulement = new Roulement();
            $roulement->setMatinSoir($matinSoir);
            $roulement->setAgent($agent);
            $roulement->setDate($date);
            $roulement->setService($service);
            $roulement->setPriseDeService($priseService);
            $roulement->setFinDeService($finService);

            $this->roulements[$matinSoir] = $roulement;

        } else {

            $this->roulements[$matinSoir]->setAgent($agent);
            $this->roulements[$matinSoir]->setDate($date);
            $this->roulements[$matinSoir]->setService($service);
            $this->roulements[$matinSoir]->setPriseDeService($priseService);
            $this->roulements[$matinSoir]->setFinDeService($finService);

        }

        return $this->roulements[$matinSoir];
    }

    /**
     * @return Roulement[] Returns an array of User objects
     */
    public function loadAll(DateTime $date): array
    {
        return $this
            ->createQueryBuilder('r', 'r.matin_soir')
            ->andWhere('r.date = :date')
            ->setParameter('date', $date->format('Y-m-d'))
            ->getQuery()
            ->getResult()
        ;
    }

    ///////////////////////////////////////////////////////////////////////

    //    /**
    //     * @return Roulement[] Returns an array of Roulement objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Roulement
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
