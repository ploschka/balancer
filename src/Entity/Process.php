<?php

namespace App\Entity;

use App\Repository\ProcessRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProcessRepository::class)]
class Process
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $memory = null;

    #[ORM\Column]
    private ?int $cpus = null;

    #[ORM\OneToOne(mappedBy: 'process', cascade: ['persist'])]
    private ?Machine $machine = null;

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

    public function getMachine(): ?Machine
    {
        return $this->machine;
    }

    public function setMachine(?Machine $machine): static
    {
        // unset the owning side of the relation if necessary
        if ($machine === null && $this->machine !== null) {
            $this->machine->setProcess(null);
        }

        // set the owning side of the relation if necessary
        if ($machine !== null && $machine->getProcess() !== $this) {
            $machine->setProcess($this);
        }

        $this->machine = $machine;

        return $this;
    }
}
