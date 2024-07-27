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
}
