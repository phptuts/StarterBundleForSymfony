<?php

namespace StarterKit\StartBundle\Security\Guard\Login;

use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Event\UserEvent;
use StarterKit\StartBundle\Security\Guard\GuardTrait;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

abstract class AbstractLoginGuard extends AbstractGuardAuthenticator
{
    use GuardTrait;

    /**
     * This event is fired when login has succeeded
     * @var string
     */
    const LOGIN_SUCCESS = 'login_success';

    /**
     * This event is fired when login has failed
     * @var string
     */
    const LOGIN_FAILURE = 'login_failure';

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var AuthResponseServiceInterface
     */
    protected $authResponseService;

    /**
     * LoginGuard constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param AuthResponseServiceInterface $authResponseService
     */
    public function __construct(
        EventDispatcherInterface $dispatcher,
        AuthResponseServiceInterface $authResponseService)
    {
        $this->dispatcher = $dispatcher;
        $this->authResponseService = $authResponseService;
    }


    /**
     * 4a) Return an authorized response.
     * Returns a json response with the token / refresh token password with user that is has been serialized.
     * This will also have the cookie for the websites as well.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();

        $this->dispatcher->dispatch(self::LOGIN_SUCCESS, new UserEvent($user));

        return $this->authResponseService->createJsonAuthResponse($user);
    }

    /**
     * 4b) Returns a 403 if the authentication failed
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->dispatcher->dispatch(self::LOGIN_FAILURE, new AuthFailedEvent($request, $exception));

        return new Response($exception->getMessage(), Response::HTTP_FORBIDDEN);
    }
}