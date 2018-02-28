<?php

namespace StarterKit\StartBundle\Entity;

use StarterKit\StartBundle\Model\Auth\AuthTokenModel;
use Doctrine\ORM\Mapping as ORM;


trait RefreshTokenTrait
{

    /**
     * @var string
     * @ORM\Column(name="refresh_token", type="string", nullable=true)
     */
    protected $refreshToken;

    /**
     * @var \DateTime
     * @ORM\Column(name="refresh_token_expire", type="datetime", nullable=true)
     */
    protected $refreshTokenExpire;

    /**
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * @param string $refreshToken
     * @return $this
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getRefreshTokenExpire()
    {
        return $this->refreshTokenExpire;
    }

    /**
     * @param \DateTime $refreshTokenExpire
     * @return $this
     */
    public function setRefreshTokenExpire($refreshTokenExpire)
    {
        $this->refreshTokenExpire = $refreshTokenExpire;

        return $this;
    }

    /**
     * Returns an auth model representing the token
     *
     * @return AuthTokenModel
     */
    public function getAuthRefreshModel()
    {
        return new AuthTokenModel($this->refreshToken, $this->refreshTokenExpire->getTimestamp());
    }

    /**
     * Returns true if the refresh token is valid
     *
     * @return bool
     */
    public function isRefreshTokenValid()
    {
        return !empty($this->getRefreshToken()) && $this->getRefreshTokenExpire() > new \DateTime();
    }
}