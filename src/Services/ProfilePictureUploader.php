<?php

// src/Service/ProfilePictureUploader.php
namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\Part\File;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProfilePictureUploader {
    public function __construct(
        private string $profilePicturesDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $profilePictureName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move($this->getTargetDirectory(), $profilePictureName);
        } catch (FileException $e) {
            throw new FileException("Une erreur est survenue.");
        }

        return $profilePictureName;
    }

    public function getTargetDirectory(): string
    {
        return $this->profilePicturesDirectory;
    }
}