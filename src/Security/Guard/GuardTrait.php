<?php

namespace StarterKit\StartBundle\Security\Guard;

use StarterKit\StartBundle\Model\Credential\CredentialInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

trait GuardTrait
{
    /**
     * @param CredentialInterface $credentials
     * @param UserProviderInterface $userProvider
     * @return \Symfony\Component\Security\Core\User\UserInterface
     */
    public function getUser($credentials, UserProviderInterface $userProvider)
    {
        return $userProvider->loadUserByUsername($credentials->getUserIdentifier());
    }

    /**
     * All Guards don't support remember me because we don't do sessions
     *
     * @return bool
     */
    public function supportsRememberMe()
    {
        return false;
    }

    /**
     * Most Guards don't have to check credentials because they are token based and our validated by the providers
     *
     * @param $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return true;
    }

    /**
     * This happens if the the responses has nothing to authenticate it and authentication is required for the end
     * point.
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response('Authentication Required', Response::HTTP_UNAUTHORIZED);
    }
}