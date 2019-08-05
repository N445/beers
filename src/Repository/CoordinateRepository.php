<?php

namespace App\Repository;

use App\Entity\Coordinate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Coordinate|null find($id, $lockMode = null, $lockVersion = null)
 * @method Coordinate|null findOneBy(array $criteria, array $orderBy = null)
 * @method Coordinate[]    findAll()
 * @method Coordinate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CoordinateRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Coordinate::class);
    }

    // /**
    //  * @return Coordinate[] Returns an array of Coordinate objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Coordinate
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
