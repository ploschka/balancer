<?php

namespace App\Repository;

use App\Entity\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Machine>
 */
class MachineRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Machine::class);
    }

    /**
     * @return Machine Returns a Machine object found by specifications
     */
    public function findOneBySpecs(int $memory, int $cpus): ?Machine
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.memory >= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('m.cpus >= :cs')
            ->setParameter('cs', $cpus)
            ->orderBy('m.memory', 'ASC')
            ->addOrderBy('m.cpus', 'ASC')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    //    public function findOneBySomeField($value): ?Machine
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
