<?php

namespace App\ExportImport;

use App\Entity\Project;
use App\Repository\LinkRepository;
use App\Repository\MediaRepository;
use League\Flysystem\FilesystemOperator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\Response\ResponseStream;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class Exporter
{
    public function __construct(
        private MediaRepository $mediaRepository,
        private LinkRepository $linkRepository,
        private FilesystemOperator $thumbnailStorage,
        private FilesystemOperator $mediaStorage,

    ) {
    }

    public function streamResponse(Project $project): StreamedResponse
    {
        $zipFile = $this->exportFiles($project);
        $fs = new Filesystem();
        if (! $fs->exists($zipFile)) {
            throw new NotFoundHttpException('file not found');
        }

        // on fixe le timeout php à 1h
        set_time_limit(3600);
        // je referme le ob_start par défaut de symfony
        ob_flush();
        // streamed response
        $response = new StreamedResponse();
        // pour virer le bufferring du serveur web (nginx par ex)
        $response->headers->set('X-Accel-Buffering', 'no');
        // pour forcer le téléchargement
        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            basename($zipFile)
        );
        $response->headers->set('Content-Disposition', $disposition);

        // le callback est la fonction qui renvoie effectivement la donnée
        $response->setCallback(function () use ($zipFile) {
            $handle = fopen($zipFile, 'rb');
            // on coupe le fichier en tronçons de 1ko (des chunks)
            $chunkSize = 1024*1024;
            while (!feof($handle)) {
                $buffer = fread($handle, $chunkSize);
                echo $buffer;
                ob_flush();
                flush();
            }
            fclose($handle);
        });
        // lance le processus (et notamment le callback)
        $response->send();
        return $response;
    }

    public function exportData(Project $project)
    {
        // on initialise le tableau
        $tab = $project->arrayExport();
        $mediaList = $this->mediaRepository->findByProject($project);
        $tab['mediaList'] = [];
        foreach ($mediaList as $media) {
            $tab['mediaList'][] = $media->arrayExport();
        }
        $linkList = $this->linkRepository->findByProject($project);
        $tab['linkList'] = [];
        foreach ($linkList as $link) {
            $tab['linkList'][] = $link->arrayExport();
        }
        return $tab;
    }

    public function exportFiles(Project $project)
    {
        $mediaList = $this->mediaRepository->findByProject($project);
        $uuid = Uuid::v4();
        $tmpDir = sys_get_temp_dir() . '/export-' . $uuid;

        $zip = new \ZipArchive();
        $zipName = sys_get_temp_dir() . '/archive-'. $uuid .'.zip';
        $zip->open($zipName, \ZipArchive::CREATE);

        $zip->addFromString('data.json', json_encode($this->exportData($project)));

        foreach ($mediaList as $media) {
            $mediaDestDir = $tmpDir . '/media/' . $media->vichDirectoryName();
            @mkdir($mediaDestDir, 0777, true);
            $stream = $this->mediaStorage->readStream($media->vichDirectoryName().'/'.$media->getMediaName());
            $file = fopen($mediaDestDir.'/'.$media->getMediaName(), 'w');
            stream_copy_to_stream($stream, $file);
            fclose($file);
            $zip->addFile($mediaDestDir.'/'.$media->getMediaName(), 'media/'.$media->vichDirectoryName().'/'.$media->getMediaName());

            $thumbnailDestDir = $tmpDir . '/thumbnail/' . $media->vichDirectoryName();
            @mkdir($thumbnailDestDir, 0777, true);
            $stream = $this->thumbnailStorage->readStream($media->vichDirectoryName().'/'.$media->getMediaName());
            $file = fopen($thumbnailDestDir.'/'.$media->getMediaName(), 'w');
            stream_copy_to_stream($stream, $file);
            fclose($file);
            $zip->addFile($thumbnailDestDir.'/'.$media->getMediaName(), 'thumbnail/'.$media->vichDirectoryName().'/'.$media->getMediaName());
        }
        $zip->close();

        $filesystem = new Filesystem();
        $filesystem->remove($tmpDir);

        return $zipName;
    }
}
