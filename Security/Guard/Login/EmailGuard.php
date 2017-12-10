<?php

namespace StarterKit\StartBundle\Security\Guard\Login;

use StarterKit\StartBundle\Model\Credential\CredentialEmailModel;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class EmailGuard
 * @package StarterKit\StartBundle\Security\Guard\Login
 */
class EmailGuard extends AbstractLoginGuard implements EmailGuardInterface
{
    /**
     * The field where the email
     * @var string
     */
    const EMAIL_FIELD = 'email';

    /**
     * The field where the password
     * @var string
     */
    const PASSWORD_FIELD = 'password';


    /**
     * @var UserPasswordEncoderInterface
     */
    protected $userPasswordEncoderFactory;

    /**
     * EmailGuard constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param AuthResponseServiceInterface $authResponseService
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                AuthResponseServiceInterface $authResponseService,
                                UserPasswordEncoderInterface $userPasswordEncoder)
    {
        parent::__construct($dispatcher, $authResponseService);
        $this->userPasswordEncoderFactory = $userPasswordEncoder;
    }

    /**
     * 1) Returns true if request has email and password in the json body
     *
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        $post = json_decode($request->getContent(), true);

        return !empty($post[self::EMAIL_FIELD]) && !empty($post[self::PASSWORD_FIELD]);
    }

    /**
     * 2) Returns CredentialEmailModel
     *
     * @param Request $request
     * @return CredentialEmailModel
     */
    public function getCredentials(Request $request)
    {
        $post = json_decode($request->getContent(), true);

        return new CredentialEmailModel($post[self::EMAIL_FIELD], $post[self::PASSWORD_FIELD]);
    }

    /**
     * 4) Returns true if the credentials are valid.
     * For token based requests, facebook, etc.  Token are validate by a 3rd party provider in the getUser function
     *
     * @param CredentialEmailModel $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        return $this->userPasswordEncoderFactory->isPasswordValid($user, $credentials->getPassword());
    }
}