<?php

namespace App\Repository;

use App\Entity\Process;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Process>
 */
class ProcessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Process::class);
    }

    /**
     * @return Process Returns a Process object found by specifications
     */
    public function findOneBySpecs(int $memory, int $cpus): ?Process
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->where('p.memory <= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('p.cpus <= :cs')
            ->setParameter('cs', $cpus)
            ->andWhere($qb->expr()->isNull('p.machine'))
            ->orderBy('p.memory', 'DESC')
            ->addOrderBy('p.cpus', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Process
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
