<?php

namespace App\Repository;

use App\Entity\Machine;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
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
     * @return Machine|null Returns a Machine object found by id or null
     */
    public function findOneById(int $id): ?Machine
    {
        $qb = $this->createQueryBuilder('m');
        return $qb->where('m.id = :mid')
            ->setParameter('mid', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return Machine Returns a Machine object found by specifications
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
     * @return Machine[] Returns an array of length <= $quantity of Machine objects found by specification except for $machine
     */
    public function findBySpecsExcept(int $memory, int $cpus, int $quantity, Machine $machine) : array|null
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
