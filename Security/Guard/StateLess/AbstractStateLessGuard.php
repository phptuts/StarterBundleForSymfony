<?php

namespace StarterKit\StartBundle\Security\Guard\StateLess;

use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Security\Guard\GuardTrait;
use StarterKit\StartBundle\Service\AuthResponseService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

abstract class AbstractStateLessGuard extends AbstractGuardAuthenticator
{
    use GuardTrait;

    /**
     * This is part of the rfc for sending token auth it's something prefixed to the token
     *
     * @var string
     */
    const BEARER = 'Bearer ';

    /**
     * This is the key the token is stored under in the header
     * @var string
     */
    const AUTHORIZATION_HEADER = 'Authorization';

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * 1) Gets the token from header and creates a CredentialToken Model
     *
     * @param Request $request
     * @return null|CredentialTokenModel
     */
    public function getCredentials(Request $request)
    {
        if ($request->headers->has(self::AUTHORIZATION_HEADER)) {

            return new CredentialTokenModel(str_replace(self::BEARER, '', $request->headers->get(self::AUTHORIZATION_HEADER)));
        }

        if ($request->cookies->has(AuthResponseService::AUTH_COOKIE)) {

            return new CredentialTokenModel($request->cookies->get(AuthResponseService::AUTH_COOKIE));
        }

        return null;
    }

    /**
     * 4a) This will always return null because we want the request to continue
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return null
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        return null;
    }
}