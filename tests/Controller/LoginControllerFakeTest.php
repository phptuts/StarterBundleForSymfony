<?php

namespace StarterKit\StartBundle\Tests\Controller;

use StarterKit\StartBundle\Controller\SecurityController;

/**
 * These controller should never be hit so we are testing that they always throw an exception
 * Class LoginControllerFakeTest
 * @package StarterKit\StartBundle\Tests\Controller
 */
class LoginControllerFakeTest extends BaseApiTestCase
{


    public function testLoginAction()
    {
        $this->expectException(\LogicException::class);
        $controller = new SecurityController();
        $controller->loginAction();
    }

    public function testOauthAction()
    {
        $this->expectException(\LogicException::class);
        $controller = new SecurityController();
        $controller->oauthAction();
    }

    public function testAccessTokenAction()
    {
        $this->expectException(\LogicException::class);
        $controller = new SecurityController();
        $controller->accessTokenAction();
    }

}