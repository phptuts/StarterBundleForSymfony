<?php

namespace StarterKit\StartBundle\Tests\Security\Guard;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Event\UserEvent;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Security\Guard\OAuthGuard;
use StarterKit\StartBundle\Service\AuthResponseService;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class OAuthGuardTest extends BaseTestCase
{
    /**
     * @var AuthResponseService|Mock
     */
    protected $authResponseService;

    /**
     * @var EventDispatcherInterface|Mock
     */
    protected $dispatcher;

    /**
     * @var \Twig_Environment|Mock
     */
    protected $twig;

    /**
     * @var OAuthGuard
     */
    protected $guard;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->authResponseService = \Mockery::mock(AuthResponseServiceInterface::class);
        $this->twig = \Mockery::mock(\Twig_Environment::class);

        $this->guard = new OAuthGuard($this->dispatcher, $this->authResponseService, $this->twig, 'login');
    }

    /**
     * Tests that a valid oauth requests returns a token model with the code
     */
    public function testGetCredentialsValidRequest()
    {
        $request =  Request::create('/oauth/slack', 'GET', ['code' => 'oauth_code']);

        $model = $this->guard->getCredentials($request);

        Assert::assertInstanceOf(CredentialTokenModel::class, $model);
        Assert::assertEquals('oauth_code', $model->getUserIdentifier());
    }

    /**
     * Tests invalid oauth requests return null
     */
    public function testGetCredentialsInValidRequest()
    {
        $request =  Request::create('/oauth/slack', 'GET');
        Assert::assertNull($this->guard->getCredentials($request));

        $request =  Request::create('/oauth/slack', 'POST', ['code' => 'ouath_code']);
        Assert::assertNull($this->guard->getCredentials($request));
    }

    /**
     * Test that getUser uses the UserProvider fed through the function
     */
    public function testGetUser()
    {
        $user = new User();
        $userProvider = \Mockery::mock(UserProviderInterface::class);
        $credentialModel = new CredentialTokenModel('token');

        $userProvider->shouldReceive('loadUserByUsername')
                ->with('token')
                ->andReturn($user);

        $this->guard->getUser($credentialModel, $userProvider);
    }

    /**
     * Tests check credentials returns true, this is because most of the guards use token auth
     */
    public function testCheckCredentialsReturnsTrue()
    {
        Assert::assertTrue($this->guard->checkCredentials(new CredentialTokenModel('token'), new User()));
    }

    /**
     * Tests that success event is fire and that the page render to redirect the user
     */
    public function testSuccessAuth()
    {
        $user = new User();
        $request =  Request::create('/oauth/slack', 'GET', ['code' => 'oauth_code']);
        $token = new PreAuthenticatedToken($user, [], 'oauth');

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->with('oauth_login_success', \Mockery::on(function(UserEvent $event) use($user) {
                Assert::assertEquals($user, $event->getUser());
                return true;
            }))
            ->once();

        $expectedResponse = new Response();

        $this->twig
            ->shouldReceive('render')
            ->once()
            ->with('@StarterKitStart/oauth-success.html.twig')
            ->andReturn('html');

        $this->authResponseService->shouldReceive('authenticateResponse')
            ->with($user, \Mockery::type(Response::class))
            ->andReturn($expectedResponse);

        $actualResponse = $this->guard->onAuthenticationSuccess($request, $token, 'oauth');

        Assert::assertEquals($expectedResponse, $actualResponse);
    }

    /**
     * Tests that failed event is fire and that the page render to redirect the user
     */
    public function testFailedAuth()
    {
        $request =  Request::create('/oauth/slack', 'GET', ['code' => 'oauth_code']);


        $this->twig
            ->shouldReceive('render')
            ->once()
            ->with('@StarterKitStart/oauth-failure.html.twig', ['login_path' => 'login'])
            ->andReturn('html');

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->with('oauth_login_failure', \Mockery::type(AuthFailedEvent::class))
            ->once();

        $response = $this->guard->onAuthenticationFailure($request, new AuthenticationException('bad'));

        Assert::assertInstanceOf(Response::class, $response);
    }

    /**
     * Tests that the page render to redirect the user
     */
    public function testStartNoCredentials()
    {
        $request =  Request::create('/oauth/slack', 'GET');
        $this->twig
            ->shouldReceive('render')
            ->once()
            ->with('@StarterKitStart/oauth-start.html.twig', ['login_path' => 'login'])
            ->andReturn('html');

        $response = $this->guard->start($request);

        Assert::assertInstanceOf(Response::class, $response);
    }

    /**
     * No guard should support remember because everything is stateless
     */
    public function testSupportsRememberMeReturnsFalse()
    {
        Assert::assertFalse($this->guard->supportsRememberMe());
    }
}