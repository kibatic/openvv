<?php

namespace ExportImport;

use App\ExportImport\Exporter;
use App\ExportImport\Importer;
use App\Repository\LinkRepository;
use App\Repository\MediaRepository;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ImporterTest extends KernelTestCase
{
    public function testImportZip()
    {
        self::bootKernel();
        $container = static::getContainer();
        $importer = $container->get(Importer::class);
        $exporter = $container->get(Exporter::class);
        $projectRepo = $container->get(ProjectRepository::class);
        $userRepo = $container->get(UserRepository::class);
        /** @var MediaRepository $mediaRepo */
        $mediaRepo = $container->get(MediaRepository::class);
        /** @var FilesystemOperator */
        $mediaStorage = $container->get('flysystem.adapter.media.storage');
        /** @var FilesystemOperator */
        $thumbnailStorage = $container->get('flysystem.adapter.media.storage');
        /** @var LinkRepository $linkRepo */
        $linkRepo = $container->get(LinkRepository::class);
        $this->assertInstanceOf(Importer::class, $importer);
        $project = $projectRepo->findOneByName('ExportedProject');
        $zipFile = $exporter->exportFiles($project);

        $alice = $userRepo->findOneByEmail('alice@example.com');
        $bob = $userRepo->findOneByEmail('bob@example.com');

        $importedProject = $importer->importProject($zipFile, $bob);

        $this->assertSame($bob, $importedProject->getOwner());
        $this->assertSame($alice, $project->getOwner());
        $this->assertSame($importedProject->getName(), $project->getName(). ' (imported)');
        $this->assertTrue($importedProject->getId() > $project->getId());

        $aliceMediaList = $mediaRepo->findByProject($project);
        $bobMediaList = $mediaRepo->findByProject($importedProject);
        $this->assertSame(5, count($aliceMediaList));
        $this->assertSame(5, count($bobMediaList));
        for($i = 0 ; $i < 5 ; $i++) {
            $aliceMedia = $aliceMediaList[$i];
            $bobMedia = $bobMediaList[$i];
            $this->assertSame($aliceMedia->getName(), $bobMedia->getName());
            $this->assertSame($aliceMedia->getMediaName(), $bobMedia->getMediaName());
            $this->assertSame($aliceMedia->getMediaSize(), $bobMedia->getMediaSize());
            $this->assertSame($aliceMedia->getOrderInProject(), $bobMedia->getOrderInProject());
            $this->assertGreaterThan($aliceMedia->getCreatedAt(), $bobMedia->getCreatedAt());
            $this->assertSame($aliceMedia->getProject(), $project);
            $this->assertSame($bobMedia->getProject(), $importedProject);
            $this->assertTrue($bobMedia->getId() > $aliceMedia->getId());
            $this->assertTrue($mediaStorage->fileExists($bobMedia->vichDirectoryName().'/'.$bobMedia->getMediaName()));
            $this->assertTrue($mediaStorage->fileExists($aliceMedia->vichDirectoryName().'/'.$aliceMedia->getMediaName()));
            $this->assertNotEquals($bobMedia->vichDirectoryName(), $aliceMedia->vichDirectoryName());
            $this->assertSame("media-test_$i.jpg", $mediaStorage->read($bobMedia->vichDirectoryName().'/'.$bobMedia->getMediaName()));
            $this->assertSame("media-test_$i.jpg", $mediaStorage->read($aliceMedia->vichDirectoryName().'/'.$aliceMedia->getMediaName()));

        }

        $aliceLinkList = $linkRepo->findByProject($project);
        $bobLinkList = $linkRepo->findByProject($importedProject);
        $this->assertSame(8, count($bobLinkList));
        $this->assertSame(8, count($aliceLinkList));
        foreach ($aliceLinkList as $aliceLink) {
            $found = false;
            foreach ($bobLinkList as $bobLink) {
                if ($aliceLink->getSourceMedia()->getName() === $bobLink->getSourceMedia()->getName() &&
                    $aliceLink->getTargetMedia()->getName() === $bobLink->getTargetMedia()->getName()
                ) {
                    $found = true;
                    break;
                }
            }
            $this->assertTrue($found);
        }
    }
}
