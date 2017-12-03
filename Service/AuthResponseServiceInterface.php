<?php


namespace StarterKit\StartBundle\Service;

use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Model\Response\ResponseAuthenticationModel;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

interface AuthResponseServiceInterface
{
    /**
     * Creates a json response that will contain new credentials for the user.
     *
     * @param BaseUser $user
     * @return JsonResponse
     */
    public function createJsonAuthResponse(BaseUser $user);

    /**
     * Creates a credentials model for the user
     *
     * @param BaseUser $user
     * @param Response $response
     * @return Response
     */
    public function authenticateResponse(BaseUser $user, Response $response);
}