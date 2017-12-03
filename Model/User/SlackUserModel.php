<?php

namespace StarterKit\StartBundle\Model\User;

/**
 * Class SlackUser
 * @package StarterKit\StartBundle\Model\User
 */
class SlackUserModel
{
    /**
     * @var string
     */
    private $slackUserId;

    /**
     * @var string
     */
    private $email;

    /**
     * SlackUser constructor.
     * @param $slackUserId
     * @param $email
     */
    public function __construct($slackUserId, $email)
    {
        $this->slackUserId = $slackUserId;
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getSlackUserId()
    {
        return $this->slackUserId;
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
        return !empty($this->email) && !empty($this->slackUserId);
    }
}