<?php

namespace StarterKit\StartBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Swagger\Annotations as SWG;

class SecurityController
{
    /**
     * @SWG\Post(
     *     tags={"security"},
     *     description="This logs a user in via email and password",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="post body",
     *          in="body",
     *          type="json",
     *          description="User data",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="email", type="string"),
     *              @SWG\Property(property="password", type="string")
     *          )
     *     ),
     *     @SWG\Response(description="Login Successful", response="201"),
     *     @SWG\Response(description="Login Failed", response="403"),
     *     @SWG\Response(description="Nothing was sent", response="401"),
     * )
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/login_check", name="_api_doc_login_check", methods={"POST"})
     *
     */
    public function loginAction()
    {
        throw new \LogicException("Should never hit this end point symfony should take this over.");
    }

    /**
     * @SWG\Get(
     *     tags={"security"},
     *     description="This logs the user in via OAuth",
     *     produces={"text/html"},
     *     @SWG\Parameter(
     *          name="code",
     *          in="query",
     *          type="string",
     *          description="This is the code that is exchange for the access token.",
     *          required=true,
     *     ),
     *     @SWG\Parameter(
     *          name="provider",
     *          in="path",
     *          type="string",
     *          default="slack",
     *          description="This is slack, linked in, or some other oauth provider",
     *          required=true,
     *     ),
     *     @SWG\Response(description="Login Successful", response="200"),
     *     @SWG\Response(description="Login Failed", response="403"),
     *     @SWG\Response(description="User hit cancelled or something got rejected.", response="401"),
     * )
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/oauth/{provider}", name="_api_doc_oauth", methods={"GET"})
     */
    public function oauthAction()
    {
        throw new \LogicException("Should never hit this end point symfony should take this over.");
    }

    /**
     *
     * @SWG\Post(
     *     tags={"security"},
     *     description="This logs the user in via something like facebook / google that short it by providing an
     * access token.",
     *     produces={"application/json"},
     *     consumes={"application/json"},
     *     @SWG\Parameter(
     *          name="post body",
     *          in="body",
     *          type="json",
     *          description="User data",
     *          required=true,
     *          @SWG\Schema(
     *              type="object",
     *              @SWG\Property(property="token", type="string"),
     *          )
     *     ),
     *     @SWG\Parameter(
     *          name="provider",
     *          in="path",
     *          type="string",
     *          default="facebook",
     *          description="This is facebook / google",
     *          required=true,
     *     ),
     *     @SWG\Response(description="Login Successful", response="201"),
     *     @SWG\Response(description="Login Failed", response="403"),
     *     @SWG\Response(description="Nothing was sent", response="401"),
     * )
     *
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/access-tokens/{provider}", name="_access_doc_oauth", methods={"POST"})
     */
    public function accessTokenAction()
    {
        throw new \LogicException("Should never hit this end point symfony should take this over.");
    }
}