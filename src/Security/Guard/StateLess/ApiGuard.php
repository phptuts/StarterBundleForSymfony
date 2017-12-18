<?php

namespace StarterKit\StartBundle\Security\Guard\StateLess;

use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Security\Guard\GuardTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class ApiGuard
 * @package StarterKit\StartBundle\Security\Guard\StateLess
 */
class ApiGuard extends AbstractStateLessGuard implements ApiGuardInterface
{
    use GuardTrait;

    /**
     * @var string The event fired when auth fails
     */
    const API_GUARD_FAILED = 'api_guard_failed';

    /**
     * 4a ) This is fired when authentication fails.  This can be caused by expired tokens, deleted users, etc
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->dispatcher->dispatch(self::API_GUARD_FAILED, new AuthFailedEvent($request, $exception));

        return $this->removeAuthCookieFromResponse(new Response('Authentication Failed', Response::HTTP_FORBIDDEN));
    }
}