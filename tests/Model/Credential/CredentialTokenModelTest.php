<?php


namespace StarterKit\StartBundle\Tests\Model\Credential;


use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Tests\BaseTestCase;

class CredentialTokenModelTest extends BaseTestCase
{
    public function testModel()
    {
        $model = new CredentialTokenModel('token');
        Assert::assertEquals('token', $model->getUserIdentifier());
        Assert::assertEquals('token',$model->getToken());
    }
}