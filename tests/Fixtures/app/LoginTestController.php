<?php

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

class LoginTestController
{
    /**
     * @Route(path="/login", name="test_login")
     *
     * @return Response
     */
    public function loginAction()
    {
        return new Response('Login Response');
    }

    /**
     * @Route(path="/test_homepage", name="test_homepage")
     *
     * @return Response
     */
    public function testWebsitePageAction()
    {
        return new Response('Hello World');
    }
}