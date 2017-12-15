<?php

namespace StarterKit\StartBundle\Security\Guard\Login;

use StarterKit\StartBundle\Model\Credential\CredentialTokenModel;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AccessTokenGuard
 * @package StarterKit\StartBundle\Security\Guard\Login
 */
class AccessTokenGuard extends AbstractLoginGuard implements AccessTokenGuardInterface
{

    /**
     * @var string This happens access token guard
     */
    const LOGIN_SUCCESS = 'access_token_guard';

    /**
     * @var string This happens access token guard
     */
    const LOGIN_FAILURE = 'access_token_guard';

    /**
     * The place where the token is stored
     * @var string
     */
    const TOKEN_FIELD = 'token';

    /**
     * 1) Returns true if the request has json body with token in it
     *
     * @param Request $request
     * @return bool
     */
    public function supports(Request $request)
    {
        $post = json_decode($request->getContent(), true);

        return !empty($post[self::TOKEN_FIELD]);
    }

    /**
     * 2) Returns CredentialTokenModel with json field token
     *
     * @param Request $request
     * @return CredentialTokenModel
     */
    public function getCredentials(Request $request)
    {
        $post = json_decode($request->getContent(), true);

        return new CredentialTokenModel($post[self::TOKEN_FIELD]);
    }
}