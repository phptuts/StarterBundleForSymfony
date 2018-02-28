<?php


namespace StarterKit\StartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait FacebookTrait
{
    /**
     * This would be something like their facebook user id
     *
     * @var string
     *
     * @ORM\Column(name="facebook_user_id", type="string", nullable=true, unique=true)
     */
    protected $facebookUserId;

    /**
     * @return string
     */
    public function getFacebookUserId()
    {
        return $this->facebookUserId;
    }

    /**
     * @param string $facebookUserId
     * @return $this
     */
    public function setFacebookUserId($facebookUserId)
    {
        $this->facebookUserId = $facebookUserId;

        return $this;
    }
}