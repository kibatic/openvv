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
    private ?float $sourcePitch = null;

    #[ORM\Column(nullable: true)]
    private ?float $sourceYaw = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetPitch = null;

    #[ORM\Column(nullable: true)]
    private ?float $targetYaw = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function isComplete()
    {
        return $this->getSourcePitch() !== null
            && $this->getSourceYaw() !== null
            && $this->getTargetPitch() !== null
            && $this->getTargetYaw() !== null;
    }

    public function arrayExport()
    {
        return [
            'id' => $this->getId(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'sourceMediaId' => $this->getSourceMedia()->getId(),
            'targetMediaId' => $this->getTargetMedia()->getId(),
            'sourcePitch' => $this->getSourcePitch(),
            'sourceYaw' => $this->getSourceYaw(),
            'targetPitch' => $this->getTargetPitch(),
            'targetYaw' => $this->getTargetYaw(),
        ];
    }

    public function arrayImport(array $data)
    {
        $this->setSourcePitch($data['sourcePitch']);
        $this->setSourceYaw($data['sourceYaw']);
        $this->setTargetPitch($data['targetPitch']);
        $this->setTargetYaw($data['targetYaw']);
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

    public function getSourcePitch(): ?float
    {
        return $this->sourcePitch ?? 0;
    }

    public function setSourcePitch(?float $sourcePitch): self
    {
        $this->sourcePitch = $sourcePitch;

        return $this;
    }

    public function getSourceYaw(): ?float
    {
        return $this->sourceYaw ?? 0;
    }

    public function setSourceYaw(?float $sourceYaw): self
    {
        $this->sourceYaw = $sourceYaw;

        return $this;
    }

    public function getTargetPitch(): ?float
    {
        return $this->targetPitch ?? 0;
    }

    public function setTargetPitch(?float $targetPitch): self
    {
        $this->targetPitch = $targetPitch;

        return $this;
    }

    public function getTargetYaw(): ?float
    {
        return $this->targetYaw ?? 0;
    }

    public function setTargetYaw(?float $targetYaw): self
    {
        $this->targetYaw = $targetYaw;

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
