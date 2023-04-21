<?php

namespace App\Entity;

use App\Repository\PanierRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PanierRepository::class)]
class Panier
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(type: Types::DATETIME_MUTABLE,nullable: true)]
    private ?\DateTimeInterface $DateAchat = null;

    #[ORM\Column]
    private ?bool $Etat = false;

    #[ORM\OneToMany(mappedBy: 'Panier', targetEntity: ContentPanier::class)]
    private Collection $contentPaniers;

    #[ORM\ManyToOne(inversedBy: 'paniers')]
    private ?Utilisateur $Utilisateur = null;


    public function __construct()
    {
        $this->contentPaniers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getDateAchat(): ?\DateTimeInterface
    {
        return $this->DateAchat;
    }

    public function setDateAchat(\DateTimeInterface $DateAchat): self
    {
        $this->DateAchat = $DateAchat;

        return $this;
    }

    public function isEtat(): ?bool
    {
        return $this->Etat;
    }

    public function setEtat(bool $Etat): self
    {
        $this->Etat = $Etat;

        return $this;
    }

    /**
     * @return Collection<int, ContentPanier>
     */
    public function getContentPaniers(): Collection
    {
        return $this->contentPaniers;
    }

    public function addContentPanier(ContentPanier $contentPanier): self
    {
        if (!$this->contentPaniers->contains($contentPanier)) {
            $this->contentPaniers->add($contentPanier);
            $contentPanier->setPanier($this);
        }

        return $this;
    }

    public function removeContentPanier(ContentPanier $contentPanier): self
    {
        if ($this->contentPaniers->removeElement($contentPanier)) {
            // set the owning side to null (unless already changed)
            if ($contentPanier->getPanier() === $this) {
                $contentPanier->setPanier(null);
            }
        }

        return $this;
    }

    public function getUtilisateur(): ?Utilisateur
    {
        return $this->Utilisateur;
    }

    public function setUtilisateur(?Utilisateur $Utilisateur): self
    {
        $this->Utilisateur = $Utilisateur;

        return $this;
    }

    public function __toString(): string
    {
        return $this->id;
    }
   
}