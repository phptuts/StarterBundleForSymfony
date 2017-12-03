<?php

namespace StarterKit\StartBundle\Security\Provider;

use StarterKit\StartBundle\Client\SlackClient;
use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Model\User\OAuthUser;
use StarterKit\StartBundle\Service\UserService;
use StarterKit\StartBundle\Service\UserServiceInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SlackProvider implements SlackProviderInterface
{
    use CustomProviderTrait;

    /**
     * @var SlackClient
     */
    protected $client;

    /**
     * @var UserServiceInterface
     */
    protected $userService;

    public function __construct(UserServiceInterface $userService, SlackClient $client)
    {
        $this->userService = $userService;
        $this->client = $client;
    }

    /**
     * Fetches the user from slack
     *
     * @param string $username
     * @return null|object|BaseUser
     */
    public function loadUserByUsername($username)
    {
        $slackUser = $this->client->getSlackUserFromOAuthCode($username);

        if (!$slackUser->isValid()) {
            throw new UsernameNotFoundException('No access token found.');
        }

        $user = $this->userService->findBySlackUserId($slackUser->getUserId());

        if (!empty($user)) {
            return $user;
        }

        $user = $this->userService->findUserByEmail($slackUser->getEmail());

        if (!empty($user)) {
            $user->setSlackUserId($slackUser->getUserId());

            return $user;
        }

        return $this->registerUser($slackUser);
    }

    /**
     * We register the user with their google id and email.
     *
     * @param OAuthUser $slackUser
     * @return BaseUser
     */
    protected function registerUser(OAuthUser $slackUser)
    {
        $className = $this->userService->getUserClass();
        /** @var BaseUser $user */
        $user = (new $className());
        $user->setEmail($slackUser->getEmail())
            ->setSlackUserId($slackUser->getUserId())
            ->setPlainPassword(base64_encode(random_bytes(20)));

        return $this->userService->registerUser($user, UserService::SOURCE_TYPE_SLACK);
    }
}