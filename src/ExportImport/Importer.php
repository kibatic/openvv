<?php

namespace App\ExportImport;

use App\Entity\Link;
use App\Entity\Media;
use App\Entity\Project;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Uid\Uuid;

class Importer
{
    public function __construct(
        private FilesystemOperator $thumbnailStorage,
        private FilesystemOperator $mediaStorage,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function importProject(string $zipFile, User $owner): Project
    {
        // extract zip file
        $destDir = sys_get_temp_dir() . '/importer-'.Uuid::v4();
        $zip = new \ZipArchive();
        $zip->open($zipFile);
        $zip->extractTo($destDir);
        $zip->close();
        $data = json_decode(file_get_contents($destDir . '/data.json'), true);

        // create project
        $project = new Project();
        $project->arrayImport($data);
        $project->setName($project->getName() . ' (imported)');
        $project->setOwner($owner);
        $this->entityManager->persist($project);

        // create mediaList
        $mediaIdMapping = [];
        foreach ($data['mediaList'] as $mediaData) {
            $media = new Media();
            $media->arrayImport($mediaData);
            $media->setProject($project);
            $this->entityManager->persist($media);
            $mediaIdMapping['old-'.$mediaData['id']] = $media;
        }

        // create linkList
        foreach ($data['linkList'] as $linkData) {
            $link = new Link();
            $link->arrayImport($linkData);
            $link->setSourceMedia($mediaIdMapping['old-'.$linkData['sourceMediaId']]);
            $link->setTargetMedia($mediaIdMapping['old-'.$linkData['targetMediaId']]);
            $this->entityManager->persist($link);
        }
        // flush to get ids
        $this->entityManager->flush();

        // copy files from the archive to the storage
        foreach ($data['mediaList'] as $oldMedia) {
            $media = $mediaIdMapping['old-'.$oldMedia['id']];
            $oldVichDir = $data['ownerId'] . '/' . $data['id'];
            // copy media
            $stream = fopen($destDir . '/media/' . $oldVichDir . '/' . $media->getMediaName(), 'r+');
            $this->mediaStorage->writeStream(
                $media->vichDirectoryName().'/'.$media->getMediaName(),
                $stream
            );
            fclose($stream);

            // copy thumbnail
            $stream = fopen($destDir . '/thumbnail/' . $oldVichDir . '/' . $media->getMediaName(), 'r+');
            $this->thumbnailStorage->writeStream(
                $media->vichDirectoryName().'/'.$media->getMediaName(),
                $stream
            );
            fclose($stream);
        }
        return $project;
    }
}
