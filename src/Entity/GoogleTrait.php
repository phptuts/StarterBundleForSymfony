<?php


namespace StarterKit\StartBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

trait GoogleTrait
{
    /**
     * This would be something like their google user id
     *
     * @var string
     *
     * @ORM\Column(name="google_user_id", type="string", nullable=true, unique=true)
     */
    protected $googleUserId;



    /**
     * @return string
     */
    public function getGoogleUserId()
    {
        return $this->googleUserId;
    }

    /**
     * @param string $googleUserId
     * @return $this
     */
    public function setGoogleUserId($googleUserId)
    {
        $this->googleUserId = $googleUserId;

        return $this;
    }
}