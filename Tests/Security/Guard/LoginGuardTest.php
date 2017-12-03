<?php

namespace StarterKit\StartBundle\Tests\Security\Guard;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Event\UserEvent;
use StarterKit\StartBundle\Model\Credential\CredentialEmailModel;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Security\Guard\LoginGuard;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class LoginGuardTest
 * @package StarterKit\StartBundle\Tests\Security\Guard        EncoderFactoryInterface $encoderFactory,
EventDispatcherInterface $dispatcher,
AuthResponseServiceInterface $authResponseService)

 */

class LoginGuardTest extends BaseTestCase
{

    /**
     * @var EncoderFactoryInterface|Mock
     */
    protected $encoderFactory;

    /**
     * @var EventDispatcherInterface|Mock
     */
    protected $dispatcher;

    /**
     * @var AuthResponseServiceInterface|Mock
     */
    protected $authResponseService;

    /**
     * @var LoginGuard
     */
    protected $guard;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->authResponseService = \Mockery::mock(AuthResponseServiceInterface::class);
        $this->encoderFactory = \Mockery::mock(EncoderFactoryInterface::class);
        $this->guard = new LoginGuard($this->encoderFactory, $this->dispatcher, $this->authResponseService);
    }

    /**
     * Tests that email / password combo creates the right CredentialModel
     */
    public function testEmailPasswordRequest()
    {
        $jsonString = json_encode(['email' => 'glaserpower@gmail.com', 'password' => 'password']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);

        $model = $this->guard->getCredentials($request);

        Assert::assertInstanceOf(CredentialEmailModel::class, $model);
        Assert::assertEquals('glaserpower@gmail.com', $model->getEmail());
        Assert::assertEquals('password', $model->getPassword());
    }

    /**
     * Tests that the token model returns the right model
     */
    public function testTokenRequest()
    {
        $jsonString = json_encode(['token' => 'fb_token']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);

        $model = $this->guard->getCredentials($request);

        Assert::assertInstanceOf(CredentialTokenModel::class, $model);
        Assert::assertEquals('fb_token', $model->getUserIdentifier());
    }

    /**
     * Test invalid request return null
     */
    public function testInvalidRequestReturnsNull()
    {
        $jsonString = json_encode(['moo' => 'fb_token']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);
        Assert::assertNull($this->guard->getCredentials($request));
    }

    /**
     * Tests that token credential model return true, this is because the validation is done in provider
     */
    public function testCheckCredentialsWithTokenReturnsTrue()
    {
        $model = new CredentialTokenModel('token');
        Assert::assertTrue($this->guard->checkCredentials($model, new User()));
    }

    /**
     * Tests that validation is by the encoder
     */
    public function testCheckCredentialsWithEmailPassword()
    {
        $user = new User();
        $user->setPassword('pass');
        $encoder = \Mockery::mock(PasswordEncoderInterface::class);
        $encoder->shouldReceive('isPasswordValid', 'pass', 'no_hash_pass', null)->andReturn(true);
        $model = new CredentialEmailModel('email', 'no_hash_pass');

        $this->encoderFactory->shouldReceive('getEncoder')->with($user)->andReturn($encoder);

        Assert::assertTrue($this->guard->checkCredentials($model, $user));
    }

    /**
     * Tests that an authenticated response is returned and event is fired
     */
    public function testAuthSuccess()
    {
        $user = new User();
        $request =  Request::create('/login_check', 'POST', ['token' => 'toke']);
        $token = new PreAuthenticatedToken($user, [], 'token');
        $expectedResponse = new Response();

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->with('login_success', \Mockery::on(function(UserEvent $event) use($user) {
                Assert::assertEquals($user, $event->getUser());
                return true;
            }))
            ->once();

        $this->authResponseService->shouldReceive('createJsonAuthResponse')
            ->once()
            ->with($user)
            ->andReturn($expectedResponse);

        $actualResponse = $this->guard->onAuthenticationSuccess($request, $token, 'facebook');

        Assert::assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Tests that an 403 response is returned and event is fired
     */
    public function testAuthFailed()
    {
        $request =  Request::create('/login_check', 'POST', ['token' => 'toke']);

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->with('login_failure', \Mockery::type(AuthFailedEvent::class))
            ->once();

       $response = $this->guard->onAuthenticationFailure($request, new AuthenticationException('blah'));

       Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    public function testStart()
    {
        $request =  Request::create('/login_check', 'POST', ['token' => 'toke']);

        $response = $this->guard->start($request);

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}