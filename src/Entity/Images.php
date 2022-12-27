<?php

namespace App\Entity;

use App\Repository\ImagesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ImagesRepository::class)]
class Images
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\ManyToOne(targetEntity: CHambre::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: false)]
    private $chambre;

    #[ORM\Column(type: 'string', length: 255)]
    private $path;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getChambre(): ?CHambre
    {
        return $this->chambre;
    }

    public function setChambre(?CHambre $chambre): self
    {
        $this->chambre = $chambre;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }
}
