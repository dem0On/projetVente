<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use phpDocumentor\Reflection\Types\Integer;

/**
 * @ORM\Entity(repositoryClass="App\Repository\LigneCommandeRepository")
 */
class LigneCommande
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\commande")
     * @ORM\JoinColumn(nullable=false)
     */
    private $commande_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Produit")
     * @ORM\JoinColumn(nullable=false)
     */
    private $produit_id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Produit")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prix;

    /**
     * @ORM\Column(type="integer")
     */
    private $quantite;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCommandeId(): ?commande
    {
        return $this->commande_id;
    }

    public function setCommandeId(?commande $commande_id): self
    {
        $this->commande_id = $commande_id;

        return $this;
    }

    public function getProduitId(): ?Produit
    {
        return $this->produit_id;
    }

    public function setProduitId(?Produit $produit_id): self
    {
        $this->produit_id = $produit_id;

        return $this;
    }

    public function getPrix(): ?Produit
    {
        return $this->prix;
    }

    public function setPrix(?Produit $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getQuantite()
    {
        return $this->quantite;
    }

    public function setQuantite($quantite)
    {
        $this->quantite = $quantite;

        return $this;
    }
}
