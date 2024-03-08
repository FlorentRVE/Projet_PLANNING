<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @implements PasswordUpgraderInterface<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    private ?array $users = null;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }


    public function findUserBySearch($value, $categorie): array
    {
        return $this->createQueryBuilder('u')
             ->innerJoin('u.categorie', 'c')
             ->andWhere('c.label LIKE :valueCat')
             ->andWhere('u.username LIKE :value OR :value = \'\'')
             ->setParameter('valueCat', '%'.$categorie.'%')
             ->setParameter('value', '%'.$value.'%')
             ->orderBy('u.username', 'ASC')
             ->getQuery()
             ->getResult()
        ;

    }
    public function findUserOrdered($value): array
    {
        return $this->createQueryBuilder('u')
             ->andWhere('u.username LIKE :value OR :value = \'\'')
             ->setParameter('value', '%'.$value.'%')
             ->orderBy('u.username', 'ASC')
             ->getQuery()
             ->getResult()
        ;
    }

    
    public function findOrCreate(string $matricule)
    {
        if ($this->users === null) {
            $this->users = $this->loadAll();
        }

        // on cree l'entite si elle n'existe pas
        if (!array_key_exists($matricule, $this->users)) {
            $user = (new User())->setMatricule($matricule);
            
            $this->getEntityManager()->persist($user);
            $this->getEntityManager()->flush();

            $this->users[$matricule] = $user;
        }

        return $this->services[$matricule];
    }

    /**
     * @return User[] Returns an array of User objects
     */
    public function loadAll(): array
    {
        return $this
               ->createQueryBuilder('u', 'u.matricule')
               ->getQuery()
               ->getResult()
        ;
    }


    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
