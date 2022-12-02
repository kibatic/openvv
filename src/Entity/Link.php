<?php

namespace App\Entity;

use App\Repository\LinkRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkRepository::class)]
class Link
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'fromMeLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $sourceMedia = null;

    #[ORM\ManyToOne(inversedBy: 'toMeLinks')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Media $targetMedia = null;

    #[ORM\Column(nullable: true)]
    private ?float $sourceLatitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $sourceLongitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetLatitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetLongitude = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function isComplete()
    {
        return $this->getSourceLatitude() !== null
            && $this->getSourceLongitude() !== null
            && $this->getTargetLatitude() !== null
            && $this->getTargetLongitude() !== null;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSourceMedia(): ?Media
    {
        return $this->sourceMedia;
    }

    public function setSourceMedia(?Media $sourceMedia): self
    {
        $this->sourceMedia = $sourceMedia;

        return $this;
    }

    public function getTargetMedia(): ?Media
    {
        return $this->targetMedia;
    }

    public function setTargetMedia(?Media $targetMedia): self
    {
        $this->targetMedia = $targetMedia;

        return $this;
    }

    public function getSourceTextureX(): ?int
    {
        return $this->sourceTextureX;
    }

    public function setSourceTextureX(?int $sourceTextureX): self
    {
        $this->sourceTextureX = $sourceTextureX;

        return $this;
    }

    public function getSourceTextureY(): ?int
    {
        return $this->sourceTextureY;
    }

    public function setSourceTextureY(?int $sourceTextureY): self
    {
        $this->sourceTextureY = $sourceTextureY;

        return $this;
    }

    public function getSourceLatitude(): ?float
    {
        return $this->sourceLatitude;
    }

    public function setSourceLatitude(?float $sourceLatitude): self
    {
        $this->sourceLatitude = $sourceLatitude;

        return $this;
    }

    public function getSourceLongitude(): ?float
    {
        return $this->sourceLongitude;
    }

    public function setSourceLongitude(?float $sourceLongitude): self
    {
        $this->sourceLongitude = $sourceLongitude;

        return $this;
    }

    public function getTargetLatitude(): ?float
    {
        return $this->targetLatitude;
    }

    public function setTargetLatitude(?float $targetLatitude): self
    {
        $this->targetLatitude = $targetLatitude;

        return $this;
    }

    public function getTargetLongitude(): ?float
    {
        return $this->targetLongitude;
    }

    public function setTargetLongitude(?float $targetLongitude): self
    {
        $this->targetLongitude = $targetLongitude;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
