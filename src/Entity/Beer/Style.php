<?php

namespace App\Entity\Beer;

use App\Entity\Beer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\Beer\StyleRepository")
 */
class Style
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=150)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Beer", mappedBy="style")
     */
    private $beers;

    /**
     * Style constructor.
     * @param null $name
     */
    public function __construct($name = null)
    {
        $this->beers = new ArrayCollection();
        $this->name  = $name;
    }

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
     * @return Style
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Beer[]
     */
    public function getBeers(): Collection
    {
        return $this->beers;
    }

    /**
     * @param Beer $beer
     * @return Style
     */
    public function addBeer(Beer $beer): self
    {
        if (!$this->beers->contains($beer)) {
            $this->beers[] = $beer;
            $beer->setStyle($this);
        }

        return $this;
    }

    /**
     * @param Beer $beer
     * @return Style
     */
    public function removeBeer(Beer $beer): self
    {
        if ($this->beers->contains($beer)) {
            $this->beers->removeElement($beer);
            // set the owning side to null (unless already changed)
            if ($beer->getStyle() === $this) {
                $beer->setStyle(null);
            }
        }

        return $this;
    }
}
