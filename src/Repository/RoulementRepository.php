<?php

namespace App\Repository;

use App\Entity\Roulement;
use App\Entity\User;
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


    public function findByTri($value)
    {
        return $this->createQueryBuilder('r')
            ->select('a, r')
            ->innerJoin('r.agent', 'a')
            ->orderBy('a.username', $value)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult()
        ;
    }
    public function findByTriAndUser($value, $user)
    {
        return $this->createQueryBuilder('r')
            ->select('a, r')
            ->innerJoin('r.agent', 'a')
            ->andWhere('a.username = :u')
            ->setParameter('u', $user)
            ->orderBy('r.date', $value)
            ->getQuery()
            ->getResult()
        ;
    }

        public function findOrCreate(string $agent, \DateTime $date)
    {
        if ($this->roulements === null) {
            $this->roulements = $this->loadAll();
        }

        // on cree l'entite si elle n'existe pas
        if (!array_key_exists($agent, $this->roulements)) {
            $roulement = (new Roulement())->setAgent($agent);
            
            $this->getEntityManager()->persist($roulement);
            $this->getEntityManager()->flush();

            // $this->roulement[$matricule] = $roulement;
        }

        return $this->roulement;
    }

    /**
     * @return User[] Returns an array of User objects
     */
    public function loadAll(): array
    {
        return $this
               ->createQueryBuilder('r', 'r.agent')
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
