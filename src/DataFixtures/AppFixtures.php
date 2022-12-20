<?php

namespace App\DataFixtures;

use App\Entity\Link;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use App\Enum\ProjectRendererEnum;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Filesystem\Filesystem;

class AppFixtures extends Fixture
{
    public function __construct(
        private FilesystemOperator $thumbnailStorage,
        private FilesystemOperator $mediaStorage,
    ) {
    }
    public function load(ObjectManager $manager): void
    {
        $alice = new User();
        $alice
            ->setEmail('alice@example.com')
            // this is the hash for "testpass"
            ->setPassword('$2y$13$hYZbAxA7.ySISMjHFKey..ANu44yDe1Ce1rQ1D86k8tPFdKywYAKC')
            ->setIsVerified(true)
        ;
        $manager->persist($alice);

        $bob = new User();
        $bob
            ->setEmail('bob@example.com')
            // this is the hash for "testpass"
            ->setPassword('$2y$13$hYZbAxA7.ySISMjHFKey..ANu44yDe1Ce1rQ1D86k8tPFdKywYAKC')
            ->setIsVerified(true)
        ;
        $manager->persist($bob);

        $project = new Project();
        $project
            ->setName('ExportedProject')
            ->setOwner($alice)
            ->setShareUid('test-project')
            ->setRenderer(ProjectRendererEnum::GALLERY)
            ->setShareDurationInDays(10)
            ->setShareStartedAt(new \DateTimeImmutable())
        ;
        $manager->persist($project);

        $mediaList = $this->createMediaList($manager, $project);
        $this->createLinkList($manager, $mediaList);

        $manager->flush();

        $this->createMediaFiles($mediaList);
    }

    /**
     * @return Media[]
     */
    public function createMediaList(ObjectManager $manager, Project $project): array
    {
        $mediaList = [];
        for ($i = 0 ; $i < 5 ; $i++) {
            $media = new Media();
            $media
                ->setProject($project)
                ->setMediaName("test_$i.jpg")
                ->setMediaSize(1000)
                ->setName('media no '.$i)
                ->setOrderInProject($i)
                ->setInitialYaw(0)
                ->setInitialPitch(0)
            ;
            $mediaList[] = $media;
            $manager->persist($media);
        }
        return $mediaList;
    }

    /**
     * @param ObjectManager $manager
     * @param Media[] $mediaList
     * @return Link[]
     */
    public function createLinkList(ObjectManager $manager, array $mediaList): void
    {
        $manager->persist($this->createOneLink(0,1, $mediaList));
        $manager->persist($this->createOneLink(1,0, $mediaList));
        $manager->persist($this->createOneLink(0,2, $mediaList));
        $manager->persist($this->createOneLink(2,0, $mediaList));

        $manager->persist($this->createOneLink(0,3, $mediaList));
        $manager->persist($this->createOneLink(3,0, $mediaList));
        $manager->persist($this->createOneLink(1,3, $mediaList));
        $manager->persist($this->createOneLink(3,3, $mediaList));
    }

    public function createOneLink(int $sourceMedia, int $targetMedia, array $mediaList): Link
    {
        $link = new Link();
        $link->setSourceMedia($mediaList[$sourceMedia]);
        $link->setTargetMedia($mediaList[$targetMedia]);
        $link->setSourcePitch(0+$sourceMedia);
        $link->setSourceYaw(0+$targetMedia);
        $link->setTargetPitch($sourceMedia+1.5);
        $link->setTargetYaw($targetMedia+1.5);
        return $link;
    }

    /**
     * @param Media[] $mediaList
     */
    public function createMediaFiles(array $mediaList): void
    {
        $this->mediaStorage->deleteDirectory('/');
        $this->thumbnailStorage->deleteDirectory('/');
        foreach ($mediaList as $media) {
            $this->mediaStorage->write($media->vichDirectoryName().'/'.$media->getMediaName(), 'media-'.$media->getMediaName());
            $this->thumbnailStorage->write($media->vichDirectoryName().'/'.$media->getMediaName(), 'thumbnail-'.$media->getMediaName());
        }
    }
}
