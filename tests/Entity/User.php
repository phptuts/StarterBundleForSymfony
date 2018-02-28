<?php


namespace StarterKit\StartBundle\Tests\Entity;

use StarterKit\StartBundle\Entity\BaseUser;
use Doctrine\ORM\Mapping as ORM;
use StarterKit\StartBundle\Entity\FacebookTrait;
use StarterKit\StartBundle\Entity\GoogleTrait;
use StarterKit\StartBundle\Entity\ImageTrait;
use StarterKit\StartBundle\Entity\RefreshTokenTrait;
use StarterKit\StartBundle\Entity\SlackTrait;

/**
 * @ORM\Entity(repositoryClass="StarterKit\StartBundle\Repository\UserRepository")
 * @ORM\Table(name="TestUser")
 * @ORM\HasLifecycleCallbacks()
 *
 * Class User
 * @package StarterKit\StartBundle\Tests\Entity
 */
class User extends BaseUser
{
    use FacebookTrait;

    use GoogleTrait;

    use SlackTrait;

    use ImageTrait;

    use RefreshTokenTrait;

}