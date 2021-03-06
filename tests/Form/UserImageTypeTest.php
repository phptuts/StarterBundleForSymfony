<?php


namespace StarterKit\StartBundle\Tests;


use StarterKit\StartBundle\Tests\Entity\User;
use StarterKit\StartBundle\Form\UserImageType;
use PHPUnit\Framework\Assert;
use Symfony\Component\Form\PreloadedExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserImageTypeTest extends TypeTestCase
{
    /**
     * Allows us to mock the transformer
     * @return array
     */
    public function getExtensions()
    {
        $form = new UserImageType(User::class);

        return [
            new PreloadedExtension([$form],[]),
        ];
    }
    /**
     * TODO Figure out why travis ci does not pass file upload
     * Testing that the form compiles with the right field
     */
    public function testFormCompiles()
    {
        $form = $this->factory->create(UserImageType::class);
        $image = \Mockery::mock(UploadedFile::class);
        $form->submit([ 'image' => $image]);

        Assert::assertTrue($form->isSynchronized());

        Assert::assertArrayHasKey('image', $form->createView()->children);
    }
}