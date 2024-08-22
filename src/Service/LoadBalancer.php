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

    /**
     * Find machine that could claim $process
     * @param Process $process
     * @return Machine|null machine that claimed process
     * or null if no suitable machine found
     */
    public function findMachineForProcess(Process $process): ?Machine
    {
        $m = $this->mrep->findOneBySpecs($process->getMemory(), $process->getCpus());
        $process->setMachine($m);
        if (!is_null($m))
        {
            $m->claim($process);
        }

        return $m;
    }

    /**
     * Find processes that could be claimed by $machine
     * @param Machine $machine
     * @return Process[]|null an array of claimed processes
     * or null if no suitable processes found
     */
    public function findProcessesForMachine(Machine $machine): array|null
    {
        $ps = $this->prep->findBySpecs($machine->getMemory(), $machine->getCpus());
        $i = 0;
        $pc = count($ps);
        if (!is_null($ps))
        {
            while ($i < $pc and $machine->getFreeMemory() > 0 and $machine->getFreeCpus() > 0)
            {
                $machine->addProcess($ps[$i]);
                $machine->claim($ps[$i]);
                $i++;
            }
        }
        return $i === 0 ? null : array_slice($ps, 0, $i);
    }

    /**
     * Free process from its machine and find new processes for that machine
     * @param Process $process process to free
     * @return Process[]|null an array of new processes for machine that claimed $process
     * or null if process was already free
     */
    public function freeProcess(Process $process) : array|null
    {
        $m = $process->getMachine();
        if (is_null($m))
        {
            return null;
        }
        $m->removeProcess($process);
        $m->free($process);
        $new_p = $this->findProcessesForMachine($m);
        $process->setMachine(null);

        return $new_p ?? [];
    }

    /**
     * Free machine from its processes and find new machines for that processes
     * @param Machine $machine machine to free
     * @return Process[] an array of processes that were claimed by deleted machine
     */
    public function freeMachine(Machine $machine) : array
    {
        $ps = $machine->getProcesses();
        $len = $ps->count();
        if ($len === 0)
        {
            return [];
        }

        $fm = $this->mrep->findBySpecsExcept($ps->first()->getMemory(), $ps->first()->getCpus(), $len, $machine);

        $end = false;

        $fm = $fm ?? [];

        foreach ($ps as $p)
        {
            $claimed = false;
            if (!$end)
            {
                foreach($fm as $m)
                {
                    if ($m->getFreeMemory() >= $p->getMemory() and $m->getFreeCpus() >= $p->getCpus())
                    {
                        $p->setMachine($m);
                        $m->claim($p);
                        $claimed = true;
                        break;
                    }
                }
            }
            $end = !$claimed;
            if (!$claimed)
            {
                $p->setMachine(null);
            }
        }

        return $ps->toArray();
    }
}

