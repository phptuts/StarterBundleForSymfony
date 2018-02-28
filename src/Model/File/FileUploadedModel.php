<?php

namespace StarterKit\StartBundle\Model\File;

class FileUploadedModel
{
    /**
     * @var string the s3 amazon vendor
     */
    const VENDOR_S3 = 'S3';

    /**
     * @var string
     */
    private $fileId;

    /**
     * @var string
     */
    private $url;

    /**
     * @var string
     */
    private $vendor;

    public function __construct($fileId, $url, $vendor)
    {
        $this->fileId = $fileId;
        $this->url = $url;
        $this->vendor = $vendor;
    }

    /**
     * @return string
     */
    public function getFileId()
    {
        return $this->fileId;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getVendor()
    {
        return $this->vendor;
    }
}