<?php

namespace StarterKit\StartBundle\Tests\Event;

use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Event\AuthFailedEvent;
use StarterKit\StartBundle\Tests\BaseTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class AuthFailedEventTest extends BaseTestCase
{
    public function testAuthFailedEventModel()
    {
        $request = Request::create('/');
        $exception = new AuthenticationException('abd');
        $model = new AuthFailedEvent($request, $exception);

        Assert::assertEquals($request, $model->getRequest());
        Assert::assertEquals($exception, $model->getException());
    }

}