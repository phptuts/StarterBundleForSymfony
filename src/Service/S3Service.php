<?php

namespace StarterKit\StartBundle\Service;

use Aws\Result;
use Aws\S3\S3Client;
use StarterKit\StartBundle\Factory\S3ClientFactory;
use StarterKit\StartBundle\Model\File\FileUploadedModel;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3Service implements FileUploadInterface
{
    /**
     * @var S3Client
     */
    private $client;

    /**
     * @var string
     */
    private $env;

    /**
     * @var string
     */
    private $bucketName;

    public function __construct(S3ClientFactory $clientFactory, $bucketName, $env)
    {
        $this->client = $clientFactory->getClient();
        $this->env = $env;
        $this->bucketName = $bucketName;
    }

    /**
     * Uploads a file to amazon s3 using
     *
     * @param UploadedFile $file
     * @param string $folderPath
     * @param string $fileName
     * @return string
     */
    public function uploadFileWithFolderAndName(UploadedFile $file, $folderPath, $fileName)
    {

        $folderPath = !empty($folderPath) ?   $folderPath  . '/' : '';
        $path =   $this->env . '/' . $folderPath . $fileName . '.'. $file->guessClientExtension();
        /** @var Result $result */
        $result = $this->client->putObject([
            'ACL' => 'public-read',
            'Bucket' => $this->bucketName,
            'SourceFile' => $file->getRealPath(),
            'Key' => $path
        ]);
        
        return new FileUploadedModel($path, $result->get('ObjectURL'), FileUploadedModel::VENDOR_S3);
    }

    /**
     * Generates a name for the file and uploads it to s3
     *
     * @param UploadedFile $file
     * @return FileUploadedModel
     */
    public function uploadFile(UploadedFile $file)
    {
        $path =   $this->env . '/'  . md5(time() . random_int(1,100000)) . '.'. $file->guessClientExtension();
        /** @var Result $result */
        $result = $this->client->putObject([
            'ACL' => 'public-read',
            'Bucket' => $this->bucketName,
            'SourceFile' => $file->getRealPath(),
            'Key' => $path
        ]);

        return new FileUploadedModel($path, $result->get('ObjectURL'), FileUploadedModel::VENDOR_S3);
    }
}