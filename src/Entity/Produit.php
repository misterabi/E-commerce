<?php

namespace App\Entity;

use App\Repository\ProduitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProduitRepository::class)]
class Produit
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $nom = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $Description = null;

    #[ORM\Column]
    private ?float $Prix = null;

    #[ORM\Column]
    private ?int $Stock = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $Photo = null;

    #[ORM\ManyToMany(targetEntity: ContentPanier::class, mappedBy: 'Produit')]
    private Collection $contentPaniers;

    public function __construct()
    {
        $this->contentPaniers = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->Description;
    }

    public function setDescription(string $Description): self
    {
        $this->Description = $Description;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->Prix;
    }

    public function setPrix(float $Prix): self
    {
        $this->Prix = $Prix;

        return $this;
    }

    public function getStock(): ?int
    {
        return $this->Stock;
    }

    public function setStock(int $Stock): self
    {
        $this->Stock = $Stock;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->Photo;
    }

    public function setPhoto(?string $Photo): self
    {
        $this->Photo = $Photo;

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
            $contentPanier->addProduit($this);
        }

        return $this;
    }

    public function removeContentPanier(ContentPanier $contentPanier): self
    {
        if ($this->contentPaniers->removeElement($contentPanier)) {
            $contentPanier->removeProduit($this);
        }

        return $this;
    }

    #[ORM\PostRemove]
    public function deleteImage(){
        //verification que le produit possède une image
        if($this->Photo !== null){
            //on supprime l'image
            unlink(__DIR__.'/../../public/uploads/'.$this->Photo);
        }
    }

    public function __toString(): string
    {
        return $this->nom;
    }
}