<?php

namespace App\Entity;

use App\Enum\ProjectRendererEnum;
use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $owner = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    private ?int $shareDurationInDays = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $shareUid = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $shareStartedAt = null;

    /**
     * The renderer indicates how we will display the project : It can
     * be a single panorama, a gallery of panoramas or a virtual visit
     */
    #[ORM\Column(length: 50, nullable: true, enumType: ProjectRendererEnum::class)]
    private ?ProjectRendererEnum $renderer;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTimeImmutable());
        $this->setShareDurationInDays(30);
        $this->setRenderer(ProjectRendererEnum::SIMPLE_PANORAMA);
    }

    public function getShareEndedAt(): ?\DateTimeImmutable
    {
        if ($this->getShareStartedAt() === null) {
            return null;
        }
        return $this->getShareStartedAt()->modify('+' . $this->getShareDurationInDays() . ' days');
    }

    public function isShareActive(): bool
    {
        if ($this->getShareStartedAt() === null) {
            return false;
        }
        return $this->getShareEndedAt() > new \DateTimeImmutable();
    }

    public function getShareRemainingDays(): ?int
    {
        if ($this->getShareStartedAt() === null) {
            return null;
        }
        $now = new \DateTimeImmutable();
        $diff = $now->diff($this->getShareEndedAt());
        return $diff->days;
    }

    public function arrayExport() {
        return [
            'id' => $this->getId(),
            'ownerEmail' => $this->getOwner()->getEmail(),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'name' => $this->getName(),
            'renderer' => $this->getRenderer() ? $this->getRenderer()->value : null,
        ];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): self
    {
        $this->owner = $owner;

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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getShareDurationInDays(): ?int
    {
        return $this->shareDurationInDays;
    }

    public function setShareDurationInDays(?int $shareDurationInDays): self
    {
        $this->shareDurationInDays = $shareDurationInDays;

        return $this;
    }

    public function getShareUid(): ?string
    {
        return $this->shareUid;
    }

    public function setShareUid(?string $shareUid): self
    {
        $this->shareUid = $shareUid;

        return $this;
    }

    public function getShareStartedAt(): ?\DateTimeImmutable
    {
        return $this->shareStartedAt;
    }

    public function setShareStartedAt(?\DateTimeImmutable $shareStartedAt): self
    {
        $this->shareStartedAt = $shareStartedAt;

        return $this;
    }

    public function getRenderer(): ?ProjectRendererEnum
    {
        return $this->renderer;
    }

    public function setRenderer(?ProjectRendererEnum $renderer): self
    {
        $this->renderer = $renderer;

        return $this;
    }
}
