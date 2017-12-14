<?php

namespace StarterKit\StartBundle\Model\User;

/**
 * Class SlackUser
 * @package StarterKit\StartBundle\Model\User
 */
class OAuthUser
{
    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $email;

    /**
     * SlackUser constructor.
     * @param $userId
     * @param $email
     */
    public function __construct($userId, $email)
    {
        $this->userId = $userId;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns true if model has everything it needs to turn into a user
     *
     * @return bool
     */
    public function isValid()
    {
        return !empty($this->email) && !empty($this->userId);
    }
}