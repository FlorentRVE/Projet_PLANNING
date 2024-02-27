<?php

namespace App\Repository;

use App\Entity\Roulement;
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
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Roulement::class);
    }


    public function findByAgent($value)
    {
        return $this->createQueryBuilder('r')
            ->select('a, r')
            ->innerJoin('r.agent', 'a')
            ->andWhere(':val = \'\' OR :val LIKE a.username')
            ->setParameter('val', $value)
            ->getQuery()
            ->getResult()
        ;
    }


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
