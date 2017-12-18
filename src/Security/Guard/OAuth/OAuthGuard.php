<?php

namespace StarterKit\StartBundle\Security\Guard\OAuth;

use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Event\UserEvent;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Security\Guard\GuardTrait;
use StarterKit\StartBundle\Service\AuthResponseServiceInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;

/**
 * Class OAuthGuard
 * @package StarterKit\StartBundle\Security\Guard
 */
class OAuthGuard extends AbstractGuardAuthenticator implements OAuthGuardInterface
{
    use GuardTrait;

    /**
     * If the auth succeeded this event is fired
     * @var string
     */
    const OAUTH_LOGIN_SUCCESS = 'oauth_login_success';

    /**
     * If the auth failed this event is fired
     * @var string
     */
    const OAUTH_LOGIN_FAILURE = 'oauth_login_failure';

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var AuthResponseServiceInterface
     */
    protected $authResponseService;

    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var string
     */
    protected $loginPath;

    /**
     * OAuthGuard constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param AuthResponseServiceInterface $authResponseService
     * @param \Twig_Environment $twig
     * @param $loginPath
     */
    public function __construct(EventDispatcherInterface $dispatcher,
                                AuthResponseServiceInterface $authResponseService,
                                \Twig_Environment $twig,
                                $loginPath)
    {
        $this->dispatcher = $dispatcher;
        $this->authResponseService = $authResponseService;
        $this->twig = $twig;
        $this->loginPath = $loginPath;
    }

    public function supports(Request $request)
    {
        return $request->isMethod('GET') && $request->query->has('code');
    }

    /**
     * 2) Gets the code from response.  We check for GET because we want to reject OPTIONS request.
     *
     * @param Request $request
     * @return null|CredentialTokenModel
     */
    public function getCredentials(Request $request)
    {
        return new CredentialTokenModel($request->query->get('code'));
    }

    /**
     * 5a) Return an authorized response.
     *
     * The page rendered will redirect the user to the home page.  We can't do the RedirectResponse.
     *
     * @param Request $request
     * @param TokenInterface $token
     * @param string $providerKey
     * @return Response
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
    {
        $user = $token->getUser();

        $this->dispatcher->dispatch(self::OAUTH_LOGIN_SUCCESS, new UserEvent($user));

        return $this->authResponseService->authenticateResponse($user,
            new Response($this->twig->render('@StarterKitStart/oauth-success.html.twig')));
    }

    /**
     * 5a) Returns a 403.
     *
     * The page rendered will redirect the user to the login page.  This happens when third party api fails.
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return Response
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->dispatcher->dispatch(self::OAUTH_LOGIN_FAILURE, new AuthFailedEvent($request, $exception));

        return $this->removeAuthCookieFromResponse(new Response(
            $this->twig->render('@StarterKitStart/oauth-failure.html.twig', ['login_path' => $this->loginPath])
        ));
    }

    /**
     * This will be fired when the user clicks cancel on slack or third party site.  This page will redirect the user
     * back to the login path.
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return Response
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new Response(
            $this->twig->render('@StarterKitStart/oauth-start.html.twig', ['login_path' => $this->loginPath])
        );
    }
}