<?php

namespace Tests\ExportImport;

use App\ExportImport\Exporter;
use App\Repository\ProjectRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;

class ExporterTest extends KernelTestCase
{
    public function testExportData()
    {
        self::bootKernel();
        $container = static::getContainer();
        $exporter = $container->get(Exporter::class);
        $projectRepo = $container->get(ProjectRepository::class);
        $this->assertInstanceOf(Exporter::class, $exporter);
        $this->assertInstanceOf(ProjectRepository::class, $projectRepo);
        $project = $projectRepo->findOneByName('ExportedProject');
        $export = $exporter->exportData($project);
        $this->assertSame('ExportedProject', $export['name']);
        $this->assertSame(5, count($export['mediaList']));
        $this->assertSame(8, count($export['linkList']));
    }

    public function testExportZip()
    {
        self::bootKernel();
        $container = static::getContainer();
        $exporter = $container->get(Exporter::class);
        $projectRepo = $container->get(ProjectRepository::class);
        $this->assertInstanceOf(Exporter::class, $exporter);
        $this->assertInstanceOf(ProjectRepository::class, $projectRepo);
        $project = $projectRepo->findOneByName('ExportedProject');
        $zipFile = $exporter->exportFiles($project);
        $this->assertFileExists($zipFile);

        $destDir = sys_get_temp_dir() . '/extracted-for-text';
        $zip = new \ZipArchive();
        $zip->open($zipFile);
        $this->assertSame(11, $zip->numFiles);
        $zip->extractTo($destDir);
        $zip->close();

        $this->assertFileExists($destDir . '/data.json');
        $this->assertSame('media-test_1.jpg', file_get_contents($destDir . '/media/1/1/test_1.jpg'));
        $this->assertSame('thumbnail-test_2.jpg', file_get_contents($destDir . '/thumbnail/1/1/test_2.jpg'));
        $this->assertEquals($exporter->exportData($project), json_decode(file_get_contents($destDir . '/data.json'), true));

        $filesystem = new Filesystem();
        $filesystem->remove($destDir);
    }
}
