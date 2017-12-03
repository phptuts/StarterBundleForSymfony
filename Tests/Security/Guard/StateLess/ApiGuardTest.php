<?php

namespace StarterKit\StartBundle\Tests\Security\Guard\StateLess;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Security\Guard\StateLess\ApiGuard;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
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

    public function testGetCredentialsValidResponse()
    {
        $request = Request::create('/api/users', 'GET');
        $request->headers->set('Authorization', 'Bearer token');
        $model = $this->guard->getCredentials($request);

        Assert::assertEquals('token', $model->getUserIdentifier());
    }

    public function testNonApiResponseReturnsNull()
    {
        $request = Request::create('/api/users', 'POST');
        Assert::assertNull($this->guard->getCredentials($request));
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

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->with('api_guard_failed', \Mockery::type(AuthFailedEvent::class))
            ->once();

        $response = $this->guard->onAuthenticationFailure($request, new AuthenticationException('bad'));

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }
}