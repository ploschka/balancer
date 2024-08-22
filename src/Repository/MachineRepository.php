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
     * Find machine by id
     * @param int $id
     * @return Machine|null suitable machine
     * or null if none found
     */
    public function findById(int $id): ?Machine
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.id = :mid')
            ->setParameter('mid', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Find one machine by specifications
     * @param int $memory
     * @param int $cpus
     * @return Machine|null suitable machine
     * or null if none found
     */
    public function findOneBySpecs(int $memory, int $cpus): ?Machine
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.freeMemory >= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('m.freeCpus >= :cs')
            ->setParameter('cs', $cpus)
            ->orderBy($qb->expr()->quot('m.freeMemory', 'm.memory'), 'DESC')
            ->orderBy($qb->expr()->quot('m.freeCpus', 'm.cpus'), 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * Find machines by specifications except for $machine
     * @param int $memory
     * @param int $cpus
     * @param int $quantity maximum length of the returned array
     * @param Machine $machine a machine to be excluded
     * @return Machine[] an array of suitable machines with length <= $quantity
     */
    public function findBySpecsExcept(int $memory, int $cpus, int $quantity, Machine $machine) : array
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.freeMemory >= :mem')
            ->setParameter('mem', $memory)
            ->andWhere('m.freeCpus >= :cs')
            ->setParameter('cs', $cpus)
            ->andWhere('m.id != :eid')
            ->setParameter('eid', $machine->getId())
            ->orderBy($qb->expr()->quot('m.freeMemory', 'm.memory'), 'DESC')
            ->orderBy($qb->expr()->quot('m.freeCpus', 'm.cpus'), 'DESC')
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
