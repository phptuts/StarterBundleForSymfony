<?php

namespace StarterKit\StartBundle\Service;

use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Entity\RefreshTokenTrait;
use StarterKit\StartBundle\Entity\SaveEntityInterface;
use StarterKit\StartBundle\Event\UserEvent;
use StarterKit\StartBundle\Exception\ProgrammerException;
use StarterKit\StartBundle\Model\Page\PageModel;
use StarterKit\StartBundle\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserService
 * @package StarterKit\StartBundle\Service\User
 */
class UserService implements UserServiceInterface
{
    /**
     * This means the user came registered the website
     * @var string
     */
    const SOURCE_TYPE_WEBSITE = 'website';

    /**
     * This means the user registered from the api
     * @var string
     */
    const SOURCE_TYPE_API = 'api';

    /**
     * This means the user registered from the google
     * @var string
     */
    const SOURCE_TYPE_GOOGLE = 'google';

    /**
     * This means the user registered from the facebook
     * @var string
     */
    const SOURCE_TYPE_FACEBOOK = 'facebook';

    /**
     * This means the user registered from the facebook
     * @var string
     */
    const SOURCE_TYPE_SLACK = 'slack';


    /**
     * This means the user was created from the admin
     * @var string
     */
    const SOURCE_TYPE_ADMIN = 'admin';


    /**
     * This is the event dispatched for registering a new user
     *
     * @var string
     */
    const REGISTER_EVENT = 'register_event';

    /**
     * This is the event dispatched when a new password token is generated
     *
     * @var string
     */
    const FORGET_PASSWORD_EVENT = 'forget_password_event';

    /**
     * @var UserPasswordEncoderInterface
     */
    protected $userPasswordEncoder;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * @var integer the number of seconds for a refresh token to live
     */
    protected $refreshTokenTTL;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var string the concrete user class
     */
    protected $userClass;
    /**
     * @var SaveServiceInterface
     */
    private $saveService;


    /**
     * UserService constructor.
     * @param EntityManagerInterface $em
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @param EventDispatcherInterface $dispatcher
     * @param SaveServiceInterface $saveService
     * @param int $refreshTokenTTL
     * @param string $userClass the fully qualified user user class
     */
    public function __construct(
        EntityManagerInterface $em,
        UserPasswordEncoderInterface $userPasswordEncoder,
        EventDispatcherInterface $dispatcher,
        SaveServiceInterface $saveService,
        $refreshTokenTTL,
        $userClass
    ) {
        $this->userPasswordEncoder = $userPasswordEncoder;
        $this->refreshTokenTTL = $refreshTokenTTL;
        $this->em = $em;
        $this->userRepository = $this->em->getRepository($userClass);
        $this->dispatcher = $dispatcher;
        $this->userClass = $userClass;
        $this->saveService = $saveService;
    }


    /**
     * Finds a user by email
     *
     * @param $email
     * @return BaseUser|null
     */
    public function findUserByEmail($email)
    {
        return $this->userRepository->findByEmail($email);
    }

    /**
     * Finds a user by their id
     *
     * @param $id
     * @return BaseUser|null|object
     */
    public function findUserById($id)
    {
        return $this->userRepository->find($id);
    }

    /**
     * Finds a user by their facebook user id
     *
     * @param $facebookUserId
     * @return BaseUser|null|object
     */
    public function findByFacebookUserId($facebookUserId)
    {
        return $this->userRepository->findByFacebookUserId($facebookUserId);
    }

    /**
     * Finds a user by their google user id
     *
     * @param $googleUserId
     * @return BaseUser|null|object
     */
    public function findByGoogleUserId($googleUserId)
    {
        return $this->userRepository->findByGoogleUserId($googleUserId);
    }

    /**
     * Finds a user by their linked in user id
     *
     * @param $slackUserId
     * @return null|object|BaseUser
     */
    public function findBySlackUserId($slackUserId)
    {
        return $this->userRepository->findBySlackUserId($slackUserId);
    }

    /**
     * Return a paginator of users
     *
     * @param $searchTerm
     * @param int $page
     * @param int $limit
     * @return PageModel
     */
    public function searchUser($searchTerm, $page = 1, $limit = 10)
    {
        $total = $this->userRepository->countNumberOfUserInSearch($searchTerm);

        $users = $this->userRepository->searchUsers($searchTerm, $page, $limit);

        $results = [];

        foreach ($users as $user) {
            $results[] = $user->listView();
        }

        return new PageModel($results, $page, $total, $limit, 'users');
    }

    /**
     * Returns true if the email exists
     *
     * @param string $email
     * @return bool
     */
    public function doesEmailExist($email)
    {
        return !empty($this->findUserByEmail($email));
    }

    /**
     * Finds the user by the forgot password token
     *
     * @param string $token
     * @return BaseUser|null
     */
    public function findUserByForgetPasswordToken($token)
    {
        return $this->userRepository->findUserByForgetPasswordToken($token);
    }

    /**
     * Return a user with a matching refresh token
     *
     * @param $token
     * @return BaseUser|null
     */
    public function findUserByValidRefreshToken($token)
    {
        return $this->userRepository->findUserByValidRefreshToken($token);
    }

    /**
     * @param BaseUser $user
     * @throws ProgrammerException
     */
    public function saveUserWithPlainPassword(BaseUser $user)
    {
        if (empty($user->getPlainPassword())) {
            throw new ProgrammerException("Plain Password must be set.", ProgrammerException::NO_PLAIN_PASSWORD_ON_USER_ENTITY_EXCEPTION_CODE);
        }

        $user->setPassword($this->userPasswordEncoder->encodePassword($user, $user->getPlainPassword()));
        $user->eraseCredentials();
        $this->save($user);
    }

    /**
     * Saves a new user to our database
     *
     * @param BaseUser $user
     * @param string $source
     *
     * @return BaseUser
     */
    public function registerUser(BaseUser $user, $source = self::SOURCE_TYPE_WEBSITE)
    {
        $user->setRoles(["ROLE_USER"])
            ->setSource($source)
            ->setEnabled(true);

        $this->saveUserWithPlainPassword($user);

        $this->dispatcher->dispatch(self::REGISTER_EVENT, new UserEvent($user));

        return $user;
    }

    /**
     * Creates a forget password token and sends a forget password email to the user
     * @param BaseUser $user
     *
     * @return BaseUser
     */
    public function forgetPassword(BaseUser $user)
    {
        $tokenExpires = (new \DateTime())->modify('+2 days');

        $user->setForgetPasswordToken(md5(uniqid(rand(), true)))
            ->setForgetPasswordExpired($tokenExpires);

        $this->save($user);

        $this->dispatcher->dispatch(self::FORGET_PASSWORD_EVENT, new UserEvent($user));

        return $user;
    }

    /**
     * Makes sure the forget password token and forget password token expiration time are set to null
     *
     * @param BaseUser $user
     */
    public function saveUserForResetPassword(BaseUser $user)
    {
        $user->setForgetPasswordToken(null)
            ->setForgetPasswordExpired(null);

        $this->saveUserWithPlainPassword($user);
    }

    /**
     * Saves the user with an updated refresh token
     *
     * @param BaseUser|RefreshTokenTrait $user
     *
     * @return BaseUser
     */
    public function updateUserRefreshToken(BaseUser $user)
    {
        if (!$user->isRefreshTokenValid()) {
            $user->setRefreshToken(bin2hex(random_bytes(90)));
        }

        $expirationDate = new \DateTime();
        $expirationDate->modify('+' . $this->refreshTokenTTL . ' seconds');
        $user->setRefreshTokenExpire($expirationDate);
        $this->save($user);

        return $user;
    }

    /**
     * Saves an entity
     *
     * @param SaveEntityInterface $entity
     */
    public function save($entity)
    {
        $this->saveService->save($entity);
    }

    /**
     * Returns the concrete user class
     *
     * @return string
     */
    public function getUserClass()
    {
        return $this->userClass;
    }


}