<?php

namespace StarterKit\StartBundle\Service;


use StarterKit\StartBundle\Model\File\FileUploadedModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileUploadInterface
{
    /**
     * Uploads a file
     *
     * @param UploadedFile $file
     * @param string $folderPath
     * @param string $fileName
     * @return FileUploadedModel the url to the file
     */
    public function uploadFileWithFolderAndName(UploadedFile $file, $folderPath, $fileName);

    /**
     * Uploads a file withouta
     *
     * @param UploadedFile $file
     * @return FileUploadedModel
     */
    public function uploadFile(UploadedFile $file);
}