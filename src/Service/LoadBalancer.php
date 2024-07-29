<?php

namespace App\Service;

use App\Entity\Machine;
use App\Entity\Process;
use App\Repository\MachineRepository;
use App\Repository\ProcessRepository;
use Doctrine\Common\Collections\ArrayCollection;
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

    public function findProcessForMachine(Machine $machine): ?Process
    {
        $p = $this->prep->findOneBySpecs($machine->getMemory(), $machine->getCpus());
        if (!is_null($p))
        {
            $machine->addProcess($p);
            $machine->claim($p);
        }
        return $p;
    }

    public function freeProcess(Process $process) : ?Machine
    {
        $m = $process->getMachine();
        if (is_null($m))
        {
            return $m;
        }
        $m->removeProcess($process);
        $m->free($process);
        $new_p = $this->findProcessForMachine($m);
        $process->setMachine(null);

        return $m;
    }

    public function freeMachine(Machine $machine) : Collection
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

        $fend = false;
        $oend = false;

        foreach ($ps as $p)
        {
            $claimed = false;
            if (!$fend)
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
            $fend = !$claimed;
            if (!$oend and !$claimed)
            {
                foreach($om as $m)
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
            $oend = !$claimed;
            if (!$claimed)
            {
                $p->setMachine(null);
            }
        }

        # foreach($fm as $m)
        # {
        #     $p = $ps->get($pcounter);
        #     $p->setMachine($m);
        #     $m->claim($p);
        #     $pcounter++;
        # }
        # foreach($om as $m)
        # {
        #     $p = $ps->get($pcounter);
        #     $p->setMachine($m);
        #     $m->claim($p);
        #     $pcounter++;
        # }
        # for($i = $pcounter; $i < $len; $i++)
        # {
        #     $ps->get($pcounter)->setMachine(null);
        # }

        return $ps;
    }
}

