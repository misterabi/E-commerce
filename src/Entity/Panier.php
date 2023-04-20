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


    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $DateAchat = null;

    #[ORM\Column]
    private ?bool $Etat = false;

    #[ORM\OneToMany(mappedBy: 'Panier', targetEntity: ContentPanier::class)]
    private Collection $contentPaniers;

    #[ORM\OneToMany(mappedBy: 'panier', targetEntity: Utilisateur::class)]
    private Collection $Utilisateur;

    public function __construct()
    {
        $this->contentPaniers = new ArrayCollection();
        $this->Utilisateur = new ArrayCollection();
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

    /**
     * @return Collection<int, Utilisateur>
     */
    public function getUtilisateur(): Collection
    {
        return $this->Utilisateur;
    }

    public function addUtilisateur(Utilisateur $utilisateur): self
    {
        if (!$this->Utilisateur->contains($utilisateur)) {
            $this->Utilisateur->add($utilisateur);
            $utilisateur->setPanier($this);
        }

        return $this;
    }

    public function removeUtilisateur(Utilisateur $utilisateur): self
    {
        if ($this->Utilisateur->removeElement($utilisateur)) {
            // set the owning side to null (unless already changed)
            if ($utilisateur->getPanier() === $this) {
                $utilisateur->setPanier(null);
            }
        }

        return $this;
    }

   
}