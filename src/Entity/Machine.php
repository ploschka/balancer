<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\Entity(repositoryClass: MachineRepository::class)]
class Machine
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $memory = null;

    #[ORM\Column]
    private ?int $cpus = null;

    /**
     * @var Collection<int, Process>
     */
    #[ORM\OneToMany(targetEntity: Process::class, mappedBy: 'machine', cascade: ['persist'])]
    #[ORM\OrderBy(['memory' => 'DESC', 'cpus' => 'DESC'])]
    private Collection $processes;

    #[ORM\Column]
    private ?int $freeMemory = null;

    #[ORM\Column]
    private ?int $freeCpus = null;

    public function __construct()
    {
        $this->processes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMemory(): ?int
    {
        return $this->memory;
    }

    public function setMemory(int $memory): static
    {
        $this->memory = $memory;

        return $this;
    }

    public function getCpus(): ?int
    {
        return $this->cpus;
    }

    public function setCpus(int $cpus): static
    {
        $this->cpus = $cpus;

        return $this;
    }

    /**
     * @return Collection<int, Process>
     */
    public function getProcesses(): Collection
    {
        return $this->processes;
    }

    public function addProcess(?Process $process): static
    {
        if (is_null($process))
        {
            return $this;
        }

        if (!$this->processes->contains($process)) {
            $this->processes->add($process);
            $process->setMachine($this);
        }

        return $this;
    }

    public function removeProcess(?Process $process): static
    {
        if (is_null($process))
        {
            return $this;
        }

        if ($this->processes->removeElement($process)) {
            // set the owning side to null (unless already changed)
            if ($process->getMachine() === $this) {
                $process->setMachine(null);
            }
        }

        return $this;
    }

    public function getFreeMemory(): ?int
    {
        return $this->freeMemory;
    }

    public function setFreeMemory(int $free_memory): static
    {
        $this->freeMemory = $free_memory;

        return $this;
    }

    private function addMemory(int $memory): static
    {
        if ($memory <= 0)
        {
            throw new Exception("Invalid memory");
        }
        if ($this->freeMemory + $memory > $this->memory)
        {
            throw new Exception("Too much memory added");
        }

        $this->freeMemory += $memory;
        return $this;
    }

    private function reduceMemory(int $memory) : static
    {
        if ($memory <= 0)
        {
            throw new Exception("Invalid memory");
        }
        if ($this->freeMemory - $memory < 0)
        {
            throw new Exception("Too much memory reduced");
        }

        $this->freeMemory -= $memory;
        return $this;
    }

    public function getFreeCpus(): ?int
    {
        return $this->freeCpus;
    }

    public function setFreeCpus(int $free_cpus): static
    {
        $this->freeCpus = $free_cpus;

        return $this;
    }

    private function addCpus(int $cpus): static
    {
        if ($cpus <= 0)
        {
            throw new Exception("Invalid cpus");
        }
        if ($this->freeCpus + $cpus > $this->cpus)
        {
            throw new Exception("Too much cpus added");
        }

        $this->freeCpus += $cpus;
        return $this;
    }

    private function reduceCpus(int $cpus) : static
    {
        if ($cpus <= 0)
        {
            throw new Exception("Invalid cpus");
        }
        if ($this->freeCpus - $cpus < 0)
        {
            throw new Exception("Too much cpus reduced");
        }

        $this->freeCpus -= $cpus;
        return $this;
    }

    public function free(?Process $p) : static
    {
        if (is_null($p))
        {
            return $this;
        }

        $this->addMemory($p->getMemory())
            ->addCpus($p->getCpus());
        return $this;
    }

    public function claim(?Process $p) : static
    {
        if (is_null($p))
        {
            return $this;
        }

        $this->reduceMemory($p->getMemory())
            ->reduceCpus($p->getCpus());
        return $this;
    }
}
