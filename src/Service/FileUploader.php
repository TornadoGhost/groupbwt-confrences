<?php

namespace App\Service;

use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    private SluggerInterface $slugger;
    private ParameterBagInterface $parameterBag;

    public function __construct(
        SluggerInterface $slugger,
        ParameterBagInterface $parameterBag
    )
    {
        $this->slugger = $slugger;
        $this->parameterBag = $parameterBag;
    }

    public function upload(UploadedFile $file): ?string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        $directory = $this->getTargetDirectory();
        $filesystem = new Filesystem();

        if (!$filesystem->exists($directory)) {
            $filesystem->mkdir($directory);
        }

        try {
            $file->move($directory, $fileName);
        } catch (FileException $e) {
            return null;
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->parameterBag->get('kernel.project_dir').'/public/uploads/reports';
    }
}
