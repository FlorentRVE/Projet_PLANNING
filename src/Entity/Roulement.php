<?php

namespace App\Entity;

use App\Repository\RoulementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RoulementRepository::class)]
class Roulement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $priseDeService = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    private ?\DateTimeInterface $finDeService = null;

    #[ORM\ManyToOne(inversedBy: 'roulements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Service $service = null;

    #[ORM\ManyToOne(inversedBy: 'roulements')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $agent = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function getPriseDeService(): ?\DateTimeInterface
    {
        return $this->priseDeService;
    }

    public function setPriseDeService(\DateTimeInterface $priseDeService): static
    {
        $this->priseDeService = $priseDeService;

        return $this;
    }

    public function getFinDeService(): ?\DateTimeInterface
    {
        return $this->finDeService;
    }

    public function setFinDeService(\DateTimeInterface $finDeService): static
    {
        $this->finDeService = $finDeService;

        return $this;
    }

    public function getService(): ?Service
    {
        return $this->service;
    }

    public function setService(?Service $service): static
    {
        $this->service = $service;

        return $this;
    }

    public function getAgent(): ?User
    {
        return $this->agent;
    }

    public function setAgent(?User $agent): static
    {
        $this->agent = $agent;

        return $this;
    }
}
