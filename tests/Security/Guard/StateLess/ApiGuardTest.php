<?php

namespace StarterKit\StartBundle\Tests\Security\Guard\StateLess;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Security\Guard\StateLess\ApiGuard;
use StarterKit\StartBundle\Service\AuthResponseService;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class ApiGuardTest extends BaseTestCase
{
    /**
     * @var EventDispatcherInterface|Mock
     */
    protected $dispatcher;

    /**
     * @var ApiGuard
     */
    protected $guard;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->guard = new ApiGuard($this->dispatcher);
    }

    public function testSupportsValidRequestReturnTrue()
    {
        $request = Request::create('/api/users', 'GET');
        $request->headers->set('Authorization', 'Bearer token');
        Assert::assertTrue($this->guard->supports($request));
    }


    public function testSupportInvalidRequestReturnFalse()
    {
        $request = Request::create('/api/users', 'POST');
        Assert::assertFalse($this->guard->supports($request));
    }

    public function testGetCredentialReturnsTokenModel()
    {
        $request = Request::create('/api/users', 'GET');
        $request->headers->set('Authorization', 'Bearer token');
        $model = $this->guard->getCredentials($request);

        Assert::assertEquals('token', $model->getUserIdentifier());
    }

    public function testOnSuccessReturnsNull()
    {
        $request =  Request::create('/api/users', 'GET');
        $token = new PreAuthenticatedToken(new User(), [], 'token');

        Assert::assertNull($this->guard->onAuthenticationSuccess($request, $token, 'api'));
    }

    public function testAuthFails()
    {
        $request =  Request::create('/api/users', 'GET');
        $request->cookies->set(AuthResponseService::AUTH_COOKIE, 'jwt_token');

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->with('api_guard_failed', \Mockery::type(AuthFailedEvent::class))
            ->once();

        $response = $this->guard->onAuthenticationFailure($request, new AuthenticationException('bad'));

        Assert::assertNull($response->headers->get(AuthResponseService::AUTH_COOKIE));
        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}