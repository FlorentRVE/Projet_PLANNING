<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
// #[ORM\Index(columns: ["label"])]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $label = null;

    #[ORM\OneToMany(targetEntity: Roulement::class, mappedBy: 'service')]
    private Collection $roulements;

    public function __construct()
    {
        $this->roulements = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return Collection<int, Roulement>
     */
    public function getRoulements(): Collection
    {
        return $this->roulements;
    }

    public function addRoulement(Roulement $roulement): static
    {
        if (!$this->roulements->contains($roulement)) {
            $this->roulements->add($roulement);
            $roulement->setService($this);
        }

        return $this;
    }

    public function removeRoulement(Roulement $roulement): static
    {
        if ($this->roulements->removeElement($roulement)) {
            // set the owning side to null (unless already changed)
            if ($roulement->getService() === $this) {
                $roulement->setService(null);
            }
        }

        return $this;
    }
}
