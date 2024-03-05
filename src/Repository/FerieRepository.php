<?php

namespace App\Repository;

use App\Entity\Ferie;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ferie>
 *
 * @method Ferie|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ferie|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ferie[]    findAll()
 * @method Ferie[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FerieRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ferie::class);
    }

    //    /**
    //     * @return Ferie[] Returns an array of Ferie objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Ferie
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
