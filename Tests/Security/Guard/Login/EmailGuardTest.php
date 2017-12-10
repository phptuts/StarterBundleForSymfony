<?php

namespace StarterKit\StartBundle\Tests\Security\Guard\Login;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Event\UserEvent;
use StarterKit\StartBundle\Model\Credential\CredentialEmailModel;
use StarterKit\StartBundle\Security\Guard\Login\EmailGuard;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class EmailGuardTest extends BaseTestCase
{
    /**
     * @var UserPasswordEncoderInterface|Mock
     */
    protected $userPasswordEncoderFactory;

    /**
     * @var EventDispatcherInterface|Mock
     */
    protected $dispatcher;

    /**
     * @var AuthResponseServiceInterface|Mock
     */
    protected $authResponseService;

    /**
     * @var EmailGuard
     */
    protected $guard;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->authResponseService = \Mockery::mock(AuthResponseServiceInterface::class);
        $this->userPasswordEncoderFactory = \Mockery::mock(UserPasswordEncoderInterface::class);
        $this->guard = new EmailGuard($this->dispatcher, $this->authResponseService, $this->userPasswordEncoderFactory);
    }

    /**
     * Test that a valid request returns true
     */
    public function testSupportWithValidRequest()
    {
        $jsonString = json_encode(['email' => 'glaserpower@gmail.com', 'password' => 'password']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);

        Assert::assertTrue($this->guard->supports($request));
    }

    /**
     * Tests that invalid requests return false
     */
    public function testSupportWithInvalidRequests()
    {
        $jsonString = json_encode(['email' => 'glaserpower@gmail.com']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);

        Assert::assertFalse($this->guard->supports($request));

        $request = Request::create('/login_check', 'POST');

        Assert::assertFalse($this->guard->supports($request));
    }

    /**
     * Tests that CredentialEmailModel is return
     */
    public function testGetCredentials()
    {
        $jsonString = json_encode(['email' => 'glaserpower@gmail.com', 'password' => 'password']);
        $request = Request::create('/login_check', 'POST', [], [], [], [], $jsonString);

        $model = $this->guard->getCredentials($request);

        Assert::assertInstanceOf(CredentialEmailModel::class, $model);
        Assert::assertEquals('glaserpower@gmail.com', $model->getEmail());
        Assert::assertEquals('password', $model->getPassword());

    }

    /**
     * Tests that validation is by the encoder
     */
    public function testCheckCredentialsWithEmailPassword()
    {
        $user = new User();
        $user->setPassword('pass');
        $model = new CredentialEmailModel('email', 'no_hash_pass');

        $this->userPasswordEncoderFactory
            ->shouldReceive('isPasswordValid')
            ->with($user, 'no_hash_pass')
            ->andReturn(true);

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

    /**
     * Tests that 401 is returned
     */
    public function testStart()
    {
        $request =  Request::create('/login_check', 'POST', ['token' => 'toke']);

        $response = $this->guard->start($request);

        Assert::assertEquals(Response::HTTP_UNAUTHORIZED, $response->getStatusCode());
    }
}