<?php

namespace StarterKit\StartBundle\Tests\Model\User;

use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Model\User\SlackUserModel;
use StarterKit\StartBundle\Tests\BaseTestCase;

class SlackUserTest extends BaseTestCase
{
    public function testInvalidUser()
    {
        $model = new SlackUserModel(null, null);

        Assert::assertFalse($model->isValid());

        $model = new SlackUserModel('3333', null);

        Assert::assertFalse($model->isValid());

        $model = new SlackUserModel(null, '333');

        Assert::assertFalse($model->isValid());
    }

    public function testValidUser()
    {
        $model = new SlackUserModel('slackUserId', 'example@gmail.com');

        Assert::assertTrue($model->isValid());
    }

    public function testGetters()
    {
        $model = new SlackUserModel('slackUserId', 'example@gmail.com');
        Assert::assertEquals('slackUserId', $model->getSlackUserId());
        Assert::assertEquals('example@gmail.com', $model->getEmail());
    }
}