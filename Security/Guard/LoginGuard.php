<?php


namespace StarterKit\StartBundle\Security\Guard;


use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Event\UserEvent;
use StarterKit\StartBundle\Model\Credential\CredentialEmailModel;
use StarterKit\StartBundle\Model\Credential\CredentialInterface;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

class LoginGuard extends AbstractGuardAuthenticator implements LoginGuardInterface
{
    use GuardTrait;

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
     * The place where the token is stored
     * @var string
     */
    const TOKEN_FIELD = 'token';

    /**
     * The field to store provider for authenticating
     * @var string
     */
    const TOKEN_TYPE_FIELD = 'type';

    /**
     * This event is fired when login has succeeded
     * @var string
     */
    const LOGIN_SUCCESS = 'login_success';

    /**
     * This event is fired when login has failed
     * @var string
     */
    const LOGIN_FAILURE = 'login_failure';

    /**
     * @var EncoderFactoryInterface
     */
    protected $encoderFactory;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var AuthResponseServiceInterface
     */
    protected $authResponseService;

    /**
     * LoginGuard constructor.
     * @param EncoderFactoryInterface $encoderFactory
     * @param EventDispatcherInterface $dispatcher
     * @param AuthResponseServiceInterface $authResponseService
     */
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        EventDispatcherInterface $dispatcher,
        AuthResponseServiceInterface $authResponseService)
    {
        $this->encoderFactory = $encoderFactory;
        $this->dispatcher = $dispatcher;
        $this->authResponseService = $authResponseService;
    }


    /**
     * 1) Find the credentials if none are found this will go to the start method
     *
     * Return a credential model if there is a token or email / password combo
     *
     * @param Request $request
     * @return null|CredentialEmailModel|CredentialTokenModel
     */
    public function getCredentials(Request $request)
    {
        $post = json_decode($request->getContent(), true);

        if (!empty($post[self::EMAIL_FIELD]) && !empty($post[self::PASSWORD_FIELD])) {
            return new CredentialEmailModel($post[self::EMAIL_FIELD], $post[self::PASSWORD_FIELD]);
        }

        if (!empty($post[self::TOKEN_FIELD])) {
            return new CredentialTokenModel($post[self::TOKEN_FIELD]);
        }


        return null;
    }

    /**
     * 3) Returns true if the credentials are valid.
     * For token based requests, facebook, etc.  Token are validate by a 3rd party provider in the getUser function
     *
     * @param CredentialInterface|CredentialEmailModel $credentials
     * @param UserInterface $user
     * @return bool
     */
    public function checkCredentials($credentials, UserInterface $user)
    {
        if ($credentials instanceof CredentialTokenModel) {
            return true;
        }

        $encoder = $this->encoderFactory->getEncoder($user);

        return $encoder->isPasswordValid($user->getPassword(), $credentials->getPassword(), $user->getSalt());
    }

    /**
     * 4a) Return an authorized response.
     * Returns a json response with the token / refresh token password with user that is has been serialized.
     * This will also have the cookie for the websites as well.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();

        $this->dispatcher->dispatch(self::LOGIN_SUCCESS, new UserEvent($user));

        return $this->authResponseService->createJsonAuthResponse($user);
    }

    /**
     * 4b) Returns a 403 if the authentication failed
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->dispatcher->dispatch(self::LOGIN_FAILURE, new AuthFailedEvent($request, $exception));

        return new Response('Authentication Failed', Response::HTTP_FORBIDDEN);
    }
}