<?php

namespace StarterKit\StartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait SlackTrait
{

    /**
     * This would be something like their slack user id
     *
     * @var string
     *
     * @ORM\Column(name="slack_user_id", type="string", nullable=true, unique=true)
     */
    protected $slackUserId;


    /**
     * @return string
     */
    public function getSlackUserId()
    {
        return $this->slackUserId;
    }

    /**
     * @param string $slackUserId
     * @return $this
     */
    public function setSlackUserId($slackUserId)
    {
        $this->slackUserId = $slackUserId;

        return $this;
    }
}