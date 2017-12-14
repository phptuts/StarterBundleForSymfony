<?php

namespace StarterKit\StartBundle\Tests;


use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\FrameworkBundle\Client;

class BaseTestCase extends WebTestCase
{
    const ENVIRONMENT = 'test';

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $environment = self::ENVIRONMENT;

    public function setUp()
    {
        $this->client = static::createClient(['environment' => $this->environment]);
        parent::setUp();
    }

    public function getContainer()
    {
        return $this->client->getContainer();
    }

    public function makeClient()
    {
        return $this->client;
    }

    /**
     * @link https://github.com/mockery/mockery/issues/376
     */
    public function tearDown()
    {
        if ($container = \Mockery::getContainer()) {
            $this->addToAssertionCount($container->mockery_getExpectationCount());
            \Mockery::close();
        }
    }

    public function setObjectId(&$object, $id)
    {
        $refObject   = new \ReflectionObject( $object );
        $refProperty = $refObject->getProperty( 'id' );
        $refProperty->setAccessible( true );
        $refProperty->setValue($object, $id);
        $refProperty->setAccessible(false);
    }
}