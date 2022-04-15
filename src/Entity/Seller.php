<?php

namespace App\Entity;

use App\Repository\SellerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=SellerRepository::class)
 */
class Seller
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @ORM\OneToMany(targetEntity=Product::class, mappedBy="seller_id")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Product", mappedBy="seller")
     */
    private $products;

    private $countOfProducts;

    public function __construct()
    {
        $this->id = new ArrayCollection();
        $this->products = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function addId(Product $id): self
    {
        if (!$this->id->contains($id)) {
            $this->id[] = $id;
            $id->setSellerId($this);
        }

        return $this;
    }

    public function removeId(Product $id): self
    {
        if ($this->id->removeElement($id)) {
            // set the owning side to null (unless already changed)
            if ($id->getSellerId() === $this) {
                $id->setSellerId(null);
            }
        }

        return $this;
    }

    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function getCountOfProducts()
    {
        return count($this->products);
    }
}
