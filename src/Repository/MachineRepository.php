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
     * @return Machine Returns a free Machine object found by specifications
     */
    public function findFreeOneBySpecs(int $memory, int $cpus): ?Machine
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.freeMemory >= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('m.freeCpus >= :cs')
            ->setParameter('cs', $cpus)
            ->andWhere('m.memory = m.freeMemory')
            ->andWhere('m.cpus = m.freeCpus')
            ->orderBy('m.freeMemory', 'ASC')
            ->addOrderBy('m.freeCpus', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
    * @return Machine Returns an occupied Machine object found by specifications
    */
    public function findOccupiedOneBySpecs(int $memory, int $cpus): ?Machine
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.freeMemory >= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('m.freeCpus >= :cs')
            ->setParameter('cs', $cpus)
            ->andWhere('m.memory != m.freeMemory')
            ->andWhere('m.cpus != m.freeCpus')
            ->orderBy('m.freeMemory', 'ASC')
            ->addOrderBy('m.freeCpus', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Machine[] Returns an array of length <= $quantity of free Machine objects found by specification except for $machine
     */
    public function findBySpecsFreeExcept(int $memory, int $cpus, int $quantity, Machine $machine) : array|null
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.freeMemory >= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('m.freeCpus >= :cs')
            ->setParameter('cs', $cpus)
            ->andWhere('m.memory = m.freeMemory')
            ->andWhere('m.cpus = m.freeCpus')
            ->andWhere('m.id != :eid')
            ->setParameter('eid', $machine->getId())
            ->orderBy('m.freeMemory', 'ASC')
            ->addOrderBy('m.freeCpus', 'ASC')
            ->setMaxResults($quantity)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @return Machine[] Returns an array of length <= $quantity of occupied Machine objects found by specification except for $machine
     */
    public function findBySpecsOccupiedExcept(int $memory, int $cpus, int $quantity, Machine $machine) : array|null
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.freeMemory >= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('m.freeCpus >= :cs')
            ->setParameter('cs', $cpus)
            ->andWhere('m.memory != m.freeMemory')
            ->andWhere('m.cpus != m.freeCpus')
            ->andWhere('m.id != :eid')
            ->setParameter('eid', $machine->getId())
            ->orderBy('m.freeMemory', 'ASC')
            ->addOrderBy('m.freeCpus', 'ASC')
            ->setMaxResults($quantity)
            ->getQuery()
            ->getResult()
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
