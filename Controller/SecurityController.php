<?php

namespace StarterKit\StartBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Routing\Annotation\Route;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

class SecurityController
{
    /**
     *
     *  This is an example of a facebook user logging in the with a token
     *  <pre> {"type" : "facebook", "token" : "sdfasdfasdfasdf" } </pre>
     *
     *  This is an example of a user using a refresh token
     *  <pre> {"type" : "refresh_token", "token" : "sdfasdfasdfasdf" } </pre>
     *
     *  This is an example of a user logging in with email and password
     *  <pre> {"email" : "example@gmail.com", "password" : "*******" } </pre>
     *
     * @ApiDoc(
     *  resource=true,
     *  description="Api Login End Point",
     *  section="Security"
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
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/oauth/{provider}", name="_api_doc_oauth", methods={"GET"})
     */
    public function oauthAction()
    {
        throw new \LogicException("Should never hit this end point symfony should take this over.");
    }

    /**
     * @Security("has_role('ROLE_USER')")
     * @Route(path="/access-tokens/{provider}", name="_access_doc_oauth", methods={"POST"})
     */
    public function accessTokenAction()
    {
        throw new \LogicException("Should never hit this end point symfony should take this over.");
    }
}