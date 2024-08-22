<?php

namespace App\Entity;

use App\Repository\ProcessRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProcessRepository::class)]
class Process
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    private ?int $memory = null;

    #[ORM\Column]
    #[Assert\Type('integer')]
    #[Assert\Positive]
    private ?int $cpus = null;

    #[ORM\ManyToOne(inversedBy: 'processes', cascade: ['persist'])]
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
        $this->machine = $machine;

        return $this;
    }
}
