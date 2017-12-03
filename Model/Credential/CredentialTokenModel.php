<?php

namespace StarterKit\StartBundle\Model\Credential;

/**
 * Class CredentialTokenModel
 * @package StarterKit\StartBundle\Model\Security
 */
class CredentialTokenModel implements CredentialInterface
{
    /**
     * @var string
     */
    private $token;


    public function __construct($token)
    {
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Returns the token as the thing that will identify the user
     *
     * @return string
     */
    public function getUserIdentifier()
    {
        return $this->getToken();
    }
}