<?php

namespace StarterKit\StartBundle\Security\Provider;

use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Exception\ProgrammerException;
use StarterKit\StartBundle\Service\JWSTokenService;
use StarterKit\StartBundle\Service\AuthTokenServiceInterface;
use StarterKit\StartBundle\Service\UserServiceInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class TokenProvider
 * @package StarterKit\StartBundle\Security\Provider
 */
class TokenProvider implements TokenProviderInterface
{
    use CustomProviderTrait;

    /**
     * @var JWSTokenService
     */
    private $authTokenService;

    /**
     * TokenProvider constructor.
     * @param UserServiceInterface $userService
     * @param AuthTokenServiceInterface $authTokenService
     */
    public function __construct(AuthTokenServiceInterface $authTokenService, UserServiceInterface $userService)
    {
        $this->authTokenService = $authTokenService;
        $this->userService = $userService;
    }

    /**
     * Returns a user from the token payload if the token is valid and user_id in the payload is found otherwise
     * throws an exception
     *
     * @param string $username
     * @return null|BaseUser
     */
    public function loadUserByUsername($username)
    {
        if (!$this->authTokenService->isValid($username)) {
            throw new UsernameNotFoundException("Invalid Token.");
        }

        try {

            return $this->authTokenService->getUser($username);
        } catch (ProgrammerException $ex) {
            throw new UsernameNotFoundException($ex->getMessage(), $ex->getCode());
        }
    }

}