<?php

namespace App\Repository;

use App\Entity\Roulement;
use App\Entity\Service;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use PhpParser\Node\Expr\Cast\String_;

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

    /////////////////////////////////////////////////////////////////////////

    public function findOrCreate(User $agent, \DateTime $date, Service $serv, $priseService, $finService)
    {
        if ($this->roulements === null) {
            $this->roulements = $this->loadAll($date);
        }

        // on cree l'entite si elle n'existe pas
        if (!array_key_exists($agent->getId(), $this->roulements)) {

            $roulement = new Roulement();
            $roulement->setAgent($agent);
            $roulement->setDate($date);
            $roulement->setService($serv);
            $roulement->setPriseDeService(DateTime::createFromFormat('H:i', $priseService));
            $roulement->setFinDeService(DateTime::createFromFormat('H:i', $finService));

            $this->roulements[$agent->getId()] = $roulement;

        } else {

            $priseServiceFromImport = DateTime::createFromFormat('H:i', $priseService);
            $serviceFromImport = $serv->getLabel();
            $priseServiceFromBDD = $this->roulements[$agent->getId()]->getPriseDeService();

            if($serviceFromImport == '809' || $serviceFromImport == '604') {
                if($priseServiceFromImport != $priseServiceFromBDD) {

                    $roulement = new Roulement();
                    $roulement->setAgent($agent);
                    $roulement->setDate($date);
                    $roulement->setService($serv);
                    $roulement->setPriseDeService(DateTime::createFromFormat('H:i', $priseService));
                    $roulement->setFinDeService(DateTime::createFromFormat('H:i', $finService));

                    if (!in_array($roulement, $this->roulements)) {
                        $this->roulements[$agent->getId()] = $roulement;
                    }
                }

            } else {

                $this->roulements[$agent->getId()]->setAgent($agent);
                $this->roulements[$agent->getId()]->setDate($date);
                $this->roulements[$agent->getId()]->setService($serv);
                $this->roulements[$agent->getId()]->setPriseDeService(DateTime::createFromFormat('H:i', $priseService));
                $this->roulements[$agent->getId()]->setFinDeService(DateTime::createFromFormat('H:i', $finService));
            }

        }

        return $this->roulements[$agent->getId()];

    }

    /**
     * @return Roulement[] Returns an array of User objects
     */
    public function loadAll(DateTime $date): array
    {
        return $this
               ->createQueryBuilder('r', 'r.agent')
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
