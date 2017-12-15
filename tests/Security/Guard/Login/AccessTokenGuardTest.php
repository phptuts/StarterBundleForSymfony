<?php

namespace StarterKit\StartBundle\Tests\Security\Guard\Login;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Security\Guard\Login\AccessTokenGuard;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccessTokenGuardTest
 * @package StarterKit\StartBundle\Tests\Security\Guard\Login
 */
class AccessTokenGuardTest extends BaseTestCase
{

    /**
     * @var EventDispatcherInterface|Mock
     */
    protected $dispatcher;

    /**
     * @var AuthResponseServiceInterface|Mock
     */
    protected $authResponseService;

    /**
     * @var AccessTokenGuard
     */
    protected $guard;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->authResponseService = \Mockery::mock(AuthResponseServiceInterface::class);
        $this->guard = new AccessTokenGuard($this->dispatcher, $this->authResponseService);
    }

    /**
     * Tests that valid request return true
     */
    public function testSupportsValidRequestReturnTrue()
    {
        $jsonString = json_encode(['token' => 'fb_token']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);
        Assert::assertTrue($this->guard->supports($request));
    }

    public function testSupportsInvalidRequestReturnFalse()
    {
        $request = Request::create('/login_check', 'POST');
        Assert::assertFalse($this->guard->supports($request));

        $jsonString = json_encode(['whatever' => 'fb_token']);
        $request = Request::create('/login_check', 'POST',  [], [], [], [], $jsonString);
        Assert::assertFalse($this->guard->supports($request));

    }

    /**
     * Tests that the token model returns the right model
     */
    public function testGetCredentialReturnsTokenModel()
    {
        $jsonString = json_encode(['token' => 'fb_token']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);

        $model = $this->guard->getCredentials($request);

        Assert::assertInstanceOf(CredentialTokenModel::class, $model);
        Assert::assertEquals('fb_token', $model->getUserIdentifier());
    }

    /**
     * Tests that check credentials return true
     */
    public function testCheckCredentialsReturnsTrue()
    {
        Assert::assertTrue($this->guard->checkCredentials(new CredentialTokenModel('token'), new User()));
    }
}