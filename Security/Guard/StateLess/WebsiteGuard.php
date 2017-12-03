<?php

namespace StarterKit\StartBundle\Security\Guard\StateLess;

use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use StarterKit\StartBundle\Service\AuthResponseService;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Class WebsiteGuard
 * @package StarterKit\StartBundle\Security\Guard\StateLess
 */
class WebsiteGuard extends AbstractStateLessGuard implements WebsiteGuardInterface
{
    /**
     * This fires when the auth fails.
     * @var string
     */
    const WEBSItE_STATELESS_GUARD_AUTH_FAILED = 'website_stateless_guard_auth_failed';

    /**
     * The url parameter to navigate the user
     *
     * @var string
     */
    const NEXT_URL_PARAMETER = 'next_url';

    /**
     * @var string This represents the login path
     */
    protected $loginPath;

    /**
     * WebsiteGuard constructor.
     * @param EventDispatcherInterface $dispatcher
     * @param $loginPath
     */
    public function __construct(EventDispatcherInterface $dispatcher, $loginPath)
    {
        parent::__construct($dispatcher);
        $this->loginPath = $loginPath;
    }


    /**
     * 4b) If the response fails it will redirect the request to the login page with next_url for redirect purposes
     *
     * @param Request $request
     * @param AuthenticationException $exception
     * @return RedirectResponse
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
    {
        $this->dispatcher->dispatch(self::WEBSItE_STATELESS_GUARD_AUTH_FAILED, new AuthFailedEvent($request, $exception));
        
        return new RedirectResponse($this->createLoginPath($request));
    }

    /**
     * If the does not have credentials redirect the request to the login page with next_url for redirect purposes
     *
     * @param Request $request
     * @param AuthenticationException|null $authException
     * @return RedirectResponse
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        return new RedirectResponse($this->createLoginPath($request));
    }

    /**
     * Returns the login path for the url and will append a next param
     *
     * @param Request $request
     * @return string
     */
    protected function createLoginPath(Request $request)
    {
        $url = $this->loginPath;
        $url .= $this->appendNextUrl($request) ?  '?'. self::NEXT_URL_PARAMETER . '=' . $request->getPathInfo() : '';

        return $url;
    }

    /**
     * Returns true if next url should be appended
     *
     * @param Request $request
     * @return bool
     */
    protected function appendNextUrl(Request $request)
    {
        return !empty($request->getPathInfo()) && strpos($request->getPathInfo(), $this->loginPath) == false;
    }
}