<?php

namespace App\Service;

use App\Entity\Machine;
use App\Entity\Process;
use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;
use Doctrine\ORM\EntityManagerInterface;

class LoadBalancer
{
    private EntityManagerInterface $em;
    private MachineRepository $mrep;
    private ProcessRepository $prep;

    public function __construct(MachineRepository $m, ProcessRepository $p, EntityManagerInterface $em)
    {
        $this->mrep = $m;
        $this->prep = $p;
        $this->em = $em;
    }

    public function findMachineForProcess(Process $process)
    {
        $m = $this->mrep->findOneBySpecs($process->getMemory(), $process->getCpus());
        $process->setMachine($m);
        $this->em->persist($process);
        $this->em->flush();
    }

    public function findProcessForMachine(Machine $machine)
    {
        $p = $this->prep->findOneBySpecs($machine->getMemory(), $machine->getCpus());
        $machine->setProcess($p);
        $this->em->persist($machine);
        $this->em->flush();
    }
}

