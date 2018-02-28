<?php


namespace StarterKit\StartBundle\Tests\Controller;


use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Controller\UserController;
use StarterKit\StartBundle\Model\File\FileUploadedModel;
use StarterKit\StartBundle\Service\AuthResponseService;
use StarterKit\StartBundle\Service\FileUploadInterface;
use StarterKit\StartBundle\Service\FormSerializer;
use StarterKit\StartBundle\Service\FileUpload;
use StarterKit\StartBundle\Service\UserService;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class ImageUploadTest extends BaseApiTestCase
{
    /**
     * @var FileUpload|Mock
     */
    protected $s3Service;

    /**
     * @var UserService|Mock
     */
    protected $userService;

    /**
     * @var AuthResponseService|Mock
     */
    protected $authResponseService;

    /**
     * @var UserController
     */
    protected $userController;

    public function setUp()
    {
        parent::setUp();

        $this->s3Service = \Mockery::mock(FileUploadInterface::class);
        $this->userService = \Mockery::mock(UserService::class);
        $this->authResponseService = \Mockery::mock(AuthResponseService::class);
        $translator = \Mockery::mock(TranslatorInterface::class);
        $translator->shouldReceive('trans')->withAnyArgs()->andReturn('form_error');
        $translator->shouldReceive('transChoice')->withAnyArgs()->andReturn('form_error');

        $this->userController = new UserController(
            new FormSerializer($translator),
            $this->userService,
            $this->authResponseService,
            $this->s3Service
            );

        $security =  \Mockery::mock(AuthorizationCheckerInterface::class);
        $security->shouldReceive('isGranted')->withAnyArgs()->andReturn(true);

        $containerMock = \Mockery::mock(ContainerInterface::class);
        $containerMock->shouldReceive('has')->with('security.authorization_checker')->andReturn(true);
        $containerMock->shouldReceive('get')->with('security.authorization_checker')->andReturn($security);

        $containerMock->shouldReceive('get')->with('form.factory')->andReturn($this->getContainer()->get('form.factory'));

        $this->userController->setContainer($containerMock);
    }

    public function testUploadImage()
    {
        $user = new User();
        $user->setEmail('blue@gmail.com');

        $this->setObjectId($user, 444);

        $image = new UploadedFile(
            dirname(__FILE__)  . '/../Mock/valid_image.png',
            'valid_image.png',
            'image/png',
            filesize(dirname(__FILE__)  . '/../Mock/valid_image.png'),
            null,
            true
        );

        $request = Request::create('/api/users/444', Request::METHOD_POST);
        $request->files->set('image',  $image);

        $fileUploadedModel = new FileUploadedModel('file_id', 'url', FileUploadedModel::VENDOR_S3);

        $this->s3Service
            ->shouldReceive('uploadFileWithFolderAndName')
            ->with(\Mockery::type(UploadedFile::class), 'profile_pics', md5(444 .'_profile_id'))
            ->once()
            ->andReturn($fileUploadedModel);


        $this->userService
            ->shouldReceive('findUserById')
            ->with(444)
            ->once()
            ->andReturn($user);

        $this->userService
            ->shouldReceive('save')
            ->with(\Mockery::on(function (User $user) {
                Assert::assertEquals('url', $user->getImageUrl());
                Assert::assertEquals('file_id', $user->getImageId());
                Assert::assertEquals('S3', $user->getImageVendor());
                return true;
            }))
            ->once();

        $response = $this->userController->imageAction($request, 444);

        Assert::assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }

    public function testImageTooLarge()
    {
        $user = new User();
        $user->setEmail('blue@gmail.com');

        $this->setObjectId($user, 444);



        $image = new UploadedFile(
            dirname(__FILE__)  . '/../Mock/image_10Mb.jpg',
            'valid_image.png',
            'image/png',
            filesize(dirname(__FILE__)  . '/../Mock/image_10Mb.jpg'),
            null,
            true
        );

        $request = Request::create('/api/users/444', Request::METHOD_POST);
        $request->files->set('image',  $image);

        $this->s3Service
            ->shouldReceive('uploadFileWithFolderAndName')
            ->withAnyArgs()
            ->never();


        $this->userService
            ->shouldReceive('findUserById')
            ->with(444)
            ->once()
            ->andReturn($user);

        $this->userService
            ->shouldReceive('save')
            ->withAnyArgs()
            ->never();

        $response = $this->userController->imageAction($request, 444);
        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $json = json_decode($response->getContent(), true);
        Assert::assertNotEmpty($json['data']['children']['image']['errors'][0]);

    }
}