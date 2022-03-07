<?php

namespace App\Repository;

use App\Entity\ViewLog;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ViewLog|null find($id, $lockMode = null, $lockVersion = null)
 * @method ViewLog|null findOneBy(array $criteria, array $orderBy = null)
 * @method ViewLog[]    findAll()
 * @method ViewLog[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ViewLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ViewLog::class);
    }

    // /**
    //  * @return ViewLog[] Returns an array of ViewLog objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('v.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?ViewLog
    {
        return $this->createQueryBuilder('v')
            ->andWhere('v.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
