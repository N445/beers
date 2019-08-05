<?php

namespace App\Entity;

use App\Entity\Beer\Category;
use App\Entity\Beer\Style;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\BeerRepository")
 */
class Beer
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="float")
     */
    private $alcohol;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $last_mod;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Beer\Style", inversedBy="beers")
     */
    private $style;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Beer\Category", inversedBy="beers")
     */
    private $category;

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Beer
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getAlcohol(): ?float
    {
        return $this->alcohol;
    }

    /**
     * @param float $alcohol
     * @return Beer
     */
    public function setAlcohol(float $alcohol): self
    {
        $this->alcohol = $alcohol;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @param null|string $description
     * @return Beer
     */
    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getLastMod(): ?\DateTimeInterface
    {
        return $this->last_mod;
    }

    /**
     * @param \DateTimeInterface $last_mod
     * @return Beer
     */
    public function setLastMod(\DateTimeInterface $last_mod): self
    {
        $this->last_mod = $last_mod;

        return $this;
    }

    /**
     * @return Style|null
     */
    public function getStyle(): ?Style
    {
        return $this->style;
    }

    /**
     * @param Style|null $style
     * @return Beer
     */
    public function setStyle(?Style $style): self
    {
        $this->style = $style;

        return $this;
    }

    /**
     * @return Category|null
     */
    public function getCategory(): ?Category
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     * @return Beer
     */
    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }
}
