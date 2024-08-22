<?php

namespace App\Repository;

use App\Entity\Process;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
     * @return Process|null Returns a Process object found by id or null
     */
    public function findOneById(int $id): ?Process
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->where('p.id = :pid')
            ->setParameter('pid', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Process[] Returns an array of Process objects found by specifications
     */
    public function findBySpecs(int $memory, int $cpus): array|null
    {
        $qb = $this->createQueryBuilder('p');
        return $qb->where('p.memory <= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('p.cpus <= :cs')
            ->setParameter('cs', $cpus)
            ->andWhere($qb->expr()->isNull('p.machine'))
            ->orderBy('p.memory', 'DESC')
            ->addOrderBy('p.cpus', 'DESC')
            ->getQuery()
            ->getResult()
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
