<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[Gedmo\SortableGroup]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    // NOTE: This is not a mapped field of entity metadata, just a simple property.
    #[Vich\UploadableField(mapping: 'media', fileNameProperty: 'mediaName', size: 'mediaSize')]
    private ?File $mediaFile = null;

    #[ORM\Column(type: 'string')]
    private ?string $mediaName = null;

    #[ORM\Column(type: 'integer')]
    private ?int $mediaSize = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $uploadedAt = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\OneToMany(mappedBy: 'sourceMedia', targetEntity: Link::class, orphanRemoval: true)]
    private Collection $fromMeLinks;

    #[ORM\OneToMany(mappedBy: 'targetMedia', targetEntity: Link::class, orphanRemoval: true)]
    private Collection $toMeLinks;

    #[ORM\Column(nullable: false)]
    #[Gedmo\SortablePosition]
    private ?int $orderInProject = null;

    #[ORM\Column(nullable: true)]
    private ?float $initialLatitude = null;

    #[ORM\Column(nullable: true)]
    private ?float $initialLongitude = null;

    // create constructor
    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->fromMeLinks = new ArrayCollection();
        $this->toMeLinks = new ArrayCollection();
        $this->orderInProject = 0;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    /**
     * used by the vichDirectoryNamer to save the media in the right directory.
     * With this directory tree, the media are saved by project owner and then by project.
     */
    public function vichDirectoryName(): string
    {
        return $this->getProject()->getOwner()->getId().'/'.$this->getProject()->getId();
    }

    public function getNodeId(): string
    {
        return 'pano-'.$this->getId();
    }

    public function getInitialPosition(): array
    {
        return [
            'pitch' => $this->getInitialLatitude() ?? 0,
            'yaw' => $this->getInitialLongitude() ?? 0,
        ];
    }

    public function arrayExport()
    {
        return [
            'id' => $this->getId(),
            'projectId' => $this->getProject()->getId(),
            'name' => $this->getName(),
            'mediaName' => $this->getMediaName(),
            'mediaSize' => $this->getMediaSize(),
            'orderInProject' => $this->getOrderInProject(),
            'uploadedAt' => $this->getUploadedAt()->format('Y-m-d H:i:s'),
            'createdAt' => $this->getCreatedAt()->format('Y-m-d H:i:s'),
            'initialLatitude' => $this->getInitialLatitude(),
            'initialLongitude' => $this->getInitialLongitude(),
        ];
    }

    public function arrayImport(array $data): void
    {
        $this->setName($data['name']);
        $this->setMediaName($data['mediaName']);
        $this->setMediaSize($data['mediaSize']);
        $this->setOrderInProject($data['orderInProject']);
        $this->setUploadedAt(new \DateTimeImmutable($data['uploadedAt']));
        $this->setInitialLatitude($data['initialLatitude']);
        $this->setInitialLongitude($data['initialLongitude']);
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(?Project $project): self
    {
        $this->project = $project;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setMediaFile(?File $mediaFile = null): void
    {
        $this->mediaFile = $mediaFile;

        if (null !== $mediaFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->uploadedAt = new \DateTimeImmutable();
        }
    }

    public function getMediaFile(): ?File
    {
        return $this->mediaFile;
    }

    public function setMediaName(?string $mediaName): self
    {
        $this->mediaName = $mediaName;
        return $this;
    }

    public function getMediaName(): ?string
    {
        return $this->mediaName;
    }

    public function setMediaSize(?int $mediaSize): self
    {
        $this->mediaSize = $mediaSize;
        return $this;
    }

    public function getMediaSize(): ?int
    {
        return $this->mediaSize;
    }

    public function getUploadedAt(): ?\DateTimeImmutable
    {
        return $this->uploadedAt;
    }

    public function setUploadedAt(?\DateTimeImmutable $uploadedAt): self
    {
        $this->uploadedAt = $uploadedAt;

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

    /**
     * @return Collection<int, Link>
     */
    public function getFromMeLinks(): Collection
    {
        return $this->fromMeLinks;
    }

    public function addFromMeLink(Link $fromMeLink): self
    {
        if (!$this->fromMeLinks->contains($fromMeLink)) {
            $this->fromMeLinks->add($fromMeLink);
            $fromMeLink->setSourceMedia($this);
        }

        return $this;
    }

    public function removeFromMeLink(Link $fromMeLink): self
    {
        if ($this->fromMeLinks->removeElement($fromMeLink)) {
            // set the owning side to null (unless already changed)
            if ($fromMeLink->getSourceMedia() === $this) {
                $fromMeLink->setSourceMedia(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Link>
     */
    public function getToMeLinks(): Collection
    {
        return $this->toMeLinks;
    }

    public function addToMeLink(Link $toMeLink): self
    {
        if (!$this->toMeLinks->contains($toMeLink)) {
            $this->toMeLinks->add($toMeLink);
            $toMeLink->setTargetMedia($this);
        }

        return $this;
    }

    public function removeToMeLink(Link $toMeLink): self
    {
        if ($this->toMeLinks->removeElement($toMeLink)) {
            // set the owning side to null (unless already changed)
            if ($toMeLink->getTargetMedia() === $this) {
                $toMeLink->setTargetMedia(null);
            }
        }

        return $this;
    }

    public function getOrderInProject(): ?int
    {
        return $this->orderInProject;
    }

    public function setOrderInProject(?int $orderInProject): self
    {
        $this->orderInProject = $orderInProject;

        return $this;
    }

    public function getInitialLatitude(): ?float
    {
        return $this->initialLatitude ?? 0;
    }

    public function setInitialLatitude(?float $initialLatitude): self
    {
        $this->initialLatitude = $initialLatitude;

        return $this;
    }

    public function getInitialLongitude(): ?float
    {
        return $this->initialLongitude ?? 0;
    }

    public function setInitialLongitude(?float $initialLongitude): self
    {
        $this->initialLongitude = $initialLongitude;

        return $this;
    }
}
