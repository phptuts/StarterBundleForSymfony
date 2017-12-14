<?php

namespace StarterKit\StartBundle\Tests\Model\User;

use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Model\User\OAuthUser;
use StarterKit\StartBundle\Tests\BaseTestCase;

class SlackUserTest extends BaseTestCase
{
    public function testInvalidUser()
    {
        $model = new OAuthUser(null, null);

        Assert::assertFalse($model->isValid());

        $model = new OAuthUser('3333', null);

        Assert::assertFalse($model->isValid());

        $model = new OAuthUser(null, '333');

        Assert::assertFalse($model->isValid());
    }

    public function testValidUser()
    {
        $model = new OAuthUser('slackUserId', 'example@gmail.com');

        Assert::assertTrue($model->isValid());
    }

    public function testGetters()
    {
        $model = new OAuthUser('slackUserId', 'example@gmail.com');
        Assert::assertEquals('slackUserId', $model->getUserId());
        Assert::assertEquals('example@gmail.com', $model->getEmail());
    }
}