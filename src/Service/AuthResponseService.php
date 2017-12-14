<?php

namespace StarterKit\StartBundle\Service;

use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Model\Response\ResponseAuthenticationModel;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Class CredentialResponseBuilderService
 * @package StarterKit\StartBundle\Service\Credential
 */
class AuthResponseService implements AuthResponseServiceInterface
{
    /***
     * @var string the name of the cookie used to store the auth token.
     */
    const AUTH_COOKIE = 'auth_cookie';

    /**
     * @var JWSTokenService
     */
    private $authTokenService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * CredentialResponseBuilderService constructor.
     * @param AuthTokenServiceInterface $authTokenService
     * @param UserService $userService
     */
    public function __construct(AuthTokenServiceInterface $authTokenService, UserService $userService)
    {
        $this->authTokenService = $authTokenService;
        $this->userService = $userService;
    }

    /**
     * Creates a json response that will contain new credentials for the user.
     *
     * @param BaseUser $user
     * @return JsonResponse|Response
     */
    public function createJsonAuthResponse(BaseUser $user)
    {
        $responseModel = $this->createResponseAuthModel($user);

        $response = new JsonResponse($responseModel->getBody(), Response::HTTP_CREATED);

        return $this->setCookieForResponse($response, $responseModel);
    }

    /**
     * Sets the auth cookie for an authenticated response
     *
     * @param BaseUser $user
     * @param Response $response
     * @return Response
     */
    public function authenticateResponse(BaseUser $user, Response $response)
    {
        return $this->setCookieForResponse($response, $this->createResponseAuthModel($user));
    }

    /**
     * Sets the auth cookie
     *
     * @param Response $response
     * @param ResponseAuthenticationModel $responseModel
     * @return Response
     */
    protected function setCookieForResponse(Response $response, ResponseAuthenticationModel $responseModel)
    {
        $response->headers->setCookie(
            new Cookie(
                self::AUTH_COOKIE,
                $responseModel->getAuthToken(),
                $responseModel->getTokenExpirationTimeStamp(),
                null,
                false,
                false
            )
        );

        return $response;
    }

    /**
     * Creates a credentials model for the user
     *
     * @param BaseUser $user
     * @return ResponseAuthenticationModel
     */
    protected function createResponseAuthModel(BaseUser $user)
    {
        $user = $this->userService->updateUserRefreshToken($user);
        $authTokenModel = $this->authTokenService->createAuthTokenModel($user);

        return new ResponseAuthenticationModel($user, $authTokenModel, $user->getAuthRefreshModel());
    }


}