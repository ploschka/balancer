<?php

namespace App\Entity;

use App\Repository\MachineRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

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
    #[ORM\OneToMany(targetEntity: Process::class, mappedBy: 'machine')]
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

    public function addProcess(Process $process): static
    {
        if (!$this->processes->contains($process)) {
            $this->processes->add($process);
            $process->setMachine($this);
        }

        return $this;
    }

    public function removeProcess(Process $process): static
    {
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

    public function getFreeCpus(): ?int
    {
        return $this->freeCpus;
    }

    public function setFreeCpus(int $free_cpus): static
    {
        $this->freeCpus = $free_cpus;

        return $this;
    }
}
