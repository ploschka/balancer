<?php

namespace App\Service;

use App\Entity\Machine;
use App\Entity\Process;
use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;

class LoadBalancer
{
    private MachineRepository $mrep;
    private ProcessRepository $prep;

    public function __construct(MachineRepository $m, ProcessRepository $p)
    {
        $this->mrep = $m;
        $this->prep = $p;
    }

    public function findMachineForProcess(Process $process)
    {
        $m = $this->mrep->findOneBySpecs($process->getMemory(), $process->getCpus());
        $process->setMachine($m);
    }

    public function findProcessForMachine(Machine $machine)
    {
        $p = $this->prep->findOneBySpecs($machine->getMemory(), $machine->getCpus());
        $machine->setProcess($p);
    }
}

