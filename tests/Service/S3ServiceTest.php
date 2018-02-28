<?php

namespace StarterKit\StartBundle\Tests\Service;

use Aws\Result;
use Aws\S3\S3Client;
use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Factory\S3ClientFactory;
use StarterKit\StartBundle\Model\File\FileUploadedModel;
use StarterKit\StartBundle\Service\FileUploadInterface;
use StarterKit\StartBundle\Service\S3Service;
use StarterKit\StartBundle\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class S3ServiceTest extends BaseTestCase
{
    /**
     * @var S3Client|Mock
     */
    protected $s3Client;

    /**
     * @var FileUploadInterface
     */
    protected $s3Service;

    public function setUp()
    {
        parent::setUp();
        $s3ClientFactory = \Mockery::mock(S3ClientFactory::class);
        $this->s3Client = \Mockery::mock(S3Client::class);
        $s3ClientFactory->shouldReceive('getClient')->once()->andReturn($this->s3Client);
        $this->s3Service = new S3Service($s3ClientFactory, 'bucket_name', 'dev');
    }

    /**
     * Tests that upload works and that we get can back a url to put into the db
     */
    public function testUploadWithFileName()
    {
        $uploadedFile = \Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getRealPath')->andReturn('path');
        $uploadedFile->shouldReceive('guessClientExtension')->andReturn('png');

        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('get')->with('ObjectURL')->andReturn('url');

        $this->s3Client->shouldReceive('putObject')->with([
            'ACL' => 'public-read',
            'Bucket' => 'bucket_name',
            'SourceFile' => 'path',
            'Key' => 'dev/profile_pic/mic.png'
        ])->andReturn($result);

        $file = $this->s3Service->uploadFileWithFolderAndName($uploadedFile, 'profile_pic', 'mic');

        Assert::assertInstanceOf(FileUploadedModel::class, $file);
        Assert::assertEquals('url', $file->getUrl());
        Assert::assertEquals('S3', $file->getVendor());
        Assert::assertEquals('dev/profile_pic/mic.png', $file->getFileId());

    }

    public function testUploadWithFileOnly()
    {
        $uploadedFile = \Mockery::mock(UploadedFile::class);
        $uploadedFile->shouldReceive('getRealPath')->andReturn('path');
        $uploadedFile->shouldReceive('guessClientExtension')->andReturn('png');

        $result = \Mockery::mock(Result::class);
        $result->shouldReceive('get')->with('ObjectURL')->andReturn('url');

        $this->s3Client->shouldReceive('putObject')->with(\Mockery::on(function($uploadConfig) {

            Assert::assertEquals('public-read', $uploadConfig['ACL']);
            Assert::assertEquals('bucket_name', $uploadConfig['Bucket']);
            Assert::assertContains('dev', $uploadConfig['Key']);
            Assert::assertContains('.png', $uploadConfig['Key']);
            Assert::assertEquals('path', $uploadConfig['SourceFile']);

            return true;
        }))->andReturn($result);

        $file = $this->s3Service->uploadFile($uploadedFile);

        Assert::assertInstanceOf(FileUploadedModel::class, $file);
        Assert::assertEquals('url', $file->getUrl());
        Assert::assertEquals('S3', $file->getVendor());
        Assert::assertContains('dev/', $file->getFileId());

    }

}