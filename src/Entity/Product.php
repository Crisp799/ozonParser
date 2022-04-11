<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ProductRepository::class)
 */
class Product
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $sku;

    /**
     * @ORM\Column(type="integer")
     */
    private $price;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $reviews_count;

    /**
     * @ORM\Column(type="integer")
     */
    private $seller_id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created_date;

    /**
     * @ORM\Column(type="datetime")
     */
    private $updated_date;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): ?int
    {
        return $this->sku;
    }

    public function setSku(int $sku): self
    {
        $this->sku = $sku;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
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

    public function getReviewsCount(): ?int
    {
        return $this->reviews_count;
    }

    public function setReviewsCount(int $reviews_count): self
    {
        $this->reviews_count = $reviews_count;

        return $this;
    }

    public function getSellerId(): ?int
    {
        return $this->seller_id;
    }

    public function setSellerId(int $seller_id): self
    {
        $this->seller_id = $seller_id;

        return $this;
    }

    public function getCreatedDate(): ?\DateTimeInterface
    {
        return $this->created_date;
    }

    public function setCreatedDate(\DateTimeInterface $created_date): self
    {
        $this->created_date = $created_date;

        return $this;
    }

    public function getUpdatedDate(): ?\DateTimeInterface
    {
        return $this->updated_date;
    }

    public function setUpdatedDate(\DateTimeInterface $updated_date): self
    {
        $this->updated_date = $updated_date;

        return $this;
    }

}
