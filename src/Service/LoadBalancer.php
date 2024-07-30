<?php

namespace App\Service;

use App\Entity\Machine;
use App\Entity\Process;
use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;
use Doctrine\Common\Collections\Collection;

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
     * @return Machine Returns Machine object that claimed process
     */
    public function findMachineForProcess(Process $process): ?Machine
    {
        $m = $this->mrep->findFreeOneBySpecs($process->getMemory(), $process->getCpus());
        if (is_null($m))
        {
            $m = $this->mrep->findOccupiedOneBySpecs($process->getMemory(), $process->getCpus());
        }
        $process->setMachine($m);
        if (!is_null($m))
        {
            $m->claim($process);
        }

        return $m;
    }

    /**
     * @return Process[] Returns an array of claimed Process objects
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
     * @return Process[] Returns an array of new processes for process machine
     */
    public function freeProcess(Process $process) : array|null
    {
        $m = $process->getMachine();
        if (is_null($m))
        {
            return $m;
        }
        $m->removeProcess($process);
        $m->free($process);
        $new_p = $this->findProcessesForMachine($m);
        $process->setMachine(null);

        return $new_p;
    }

    /**
     * @return Process[] Returns an array of processes that were claimed by deleted machine
     */
    public function freeMachine(Machine $machine) : array|null
    {
        $ps = $machine->getProcesses();
        $len = $ps->count();
        if ($len === 0)
        {
            return $ps;
        }

        $fm = $this->mrep->findBySpecsFreeExcept($ps->first()->getMemory(), $ps->first()->getCpus(), $len, $machine);
        $om = null;

        $flen = count($fm);

        if ($flen < $len)
        {
            $om = $this->mrep->findBySpecsOccupiedExcept($ps->first()->getMemory(), $ps->first()->getCpus(), $len - $flen, $machine);
        }

        $end = false;

        $combined = [];
        if (!is_null($fm))
        {
            array_merge($combined, $fm);
        }
        if (!is_null($om))
        {
            array_merge($combined, $om);
        }

        foreach ($ps as $p)
        {
            $claimed = false;
            if (!$end)
            {
                foreach($combined as $m)
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

