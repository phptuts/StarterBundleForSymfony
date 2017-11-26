<?php


namespace StarterKit\StartBundle\Service;

use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Model\Response\ResponseAuthenticationModel;
use Symfony\Component\HttpFoundation\JsonResponse;

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
     * @return ResponseAuthenticationModel
     */
    public function createResponseAuthModel(BaseUser $user);
}