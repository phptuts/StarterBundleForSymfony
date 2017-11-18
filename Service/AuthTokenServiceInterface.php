<?php

namespace StarterKit\StartBundle\Service;

use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Model\Auth\AuthTokenModel;

/**
 * Interface AuthTokenServiceInterface
 * @package StarterKit\StartBundle\Service
 */
interface AuthTokenServiceInterface
{
    /**
     * Creates a jws token model
     *
     * @param BaseUser $user
     * @return AuthTokenModel
     */
    public function createAuthTokenModel(BaseUser $user);

    /**
     * Returns true if the token is valid
     *
     * @param string $token
     * @return bool
     */
    public function isValid($token);


    /**
     * Returns the user's id from the token
     *
     * @param $token
     * @return BaseUser
     */
    public function getUser($token);

}