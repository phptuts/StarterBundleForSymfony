<?php

namespace StarterKit\StartBundle\Tests\Security\Guard\StateLess;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Security\Guard\StateLess\WebsiteGuard;
use StarterKit\StartBundle\Service\AuthResponseService;
use StarterKit\StartBundle\Tests\BaseTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class WebsiteGuardTest extends BaseTestCase
{
    /**
     * @var EventDispatcherInterface|Mock
     */
    protected $dispatcher;

    /**
     * @var WebsiteGuard
     */
    protected $guard;

    public function setUp()
    {
        parent::setUp();
        $this->dispatcher = \Mockery::mock(EventDispatcherInterface::class);
        $this->guard = new WebsiteGuard($this->dispatcher, 'login');
    }

    public function testSupportsValidRequestWithCookieReturnsTrue()
    {
        $request = Request::create('/api/users', 'GET');
        $request->cookies->set('auth_cookie', 'jwt_token');
        Assert::assertTrue($this->guard->supports($request));
    }

    public function testSupportInvalidRequestReturnFalse()
    {
        $request = Request::create('/api/users', 'POST');
        Assert::assertFalse($this->guard->supports($request));
    }

    /**
     * Tests that if auth cookie is present that get credentials returns an Model
     */
    public function testGetCredentials()
    {
        $request = Request::create('/users', 'GET');
        $request->cookies->set('auth_cookie', 'token');
        $model = $this->guard->getCredentials($request);

        Assert::assertEquals('token', $model->getUserIdentifier());
    }

    /**
     * Tests that start redirects with the next_url param
     */
    public function testStartRedirectResponse()
    {
        $request = Request::create('/users', 'GET');
        $response = $this->guard->start($request);

        Assert::assertEquals('login?next_url=/users', $response->getTargetUrl());
    }

    /**
     * Tests that start redirects with the next_url param and event fires
     */
    public function testWebsiteFailure()
    {
        $request = Request::create('/moo', 'GET');
        $request->cookies->set(AuthResponseService::AUTH_COOKIE, 'jwt_token');

        $this->dispatcher
            ->shouldReceive('dispatch')
            ->with('website_stateless_guard_auth_failed', \Mockery::type(AuthFailedEvent::class))
            ->once();

        $response = $this->guard->onAuthenticationFailure($request, new AuthenticationException('balh'));
        Assert::assertNull($response->headers->get(AuthResponseService::AUTH_COOKIE));
        Assert::assertEquals('login?next_url=/moo', $response->getTargetUrl());

    }
}