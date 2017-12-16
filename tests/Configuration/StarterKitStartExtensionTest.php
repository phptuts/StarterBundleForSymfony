<?php

namespace StarterKit\StartBundle\Tests\Configuration;


use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\DependencyInjection\StarterKitStartExtension;
use StarterKit\StartBundle\Tests\BaseTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class StarterKitStartExtensionTest extends BaseTestCase
{
    /**
     * @var StarterKitStartExtension
     */
    private $extension;

    /**
     * Root name of the configuration
     *
     * @var string
     */
    private $root;

    public function setUp()
    {
        parent::setUp();

        $this->extension = $this->getExtension();
        $this->root      = "starter_kit_start";
    }

    public function testGetConfigWithDefaultValues()
    {
        $this->extension->load(array(), $container = $this->getContainer());
        
        Assert::assertEquals("bad_pass_phrase", $container->getParameter($this->root . ".jws_pass_phrase"));
        Assert::assertEquals(5184000, $container->getParameter($this->root . ".jws_ttl"));
        Assert::assertEquals(10368000, $container->hasParameter($this->root . ".refresh_token_ttl"));
        Assert::assertEquals('AppBundle\Entity\User', $container->getParameter($this->root . ".user_class"));
        Assert::assertEquals("/login", $container->getParameter($this->root . ".login_url"));

        Assert::assertNull($container->getParameter($this->root .'.facebook_app_secret'));
        Assert::assertNull($container->getParameter($this->root .'.facebook_api_version'));
        Assert::assertNull($container->getParameter($this->root .'.facebook_app_id'));
        Assert::assertNull($container->getParameter($this->root .'.google_client_id'));
        Assert::assertNull($container->getParameter($this->root .'.aws_region'));
        Assert::assertNull($container->getParameter($this->root .'.aws_key'));
        Assert::assertNull($container->getParameter($this->root .'.aws_secret'));
        Assert::assertNull($container->getParameter($this->root .'.aws_s3_bucket_name'));
        Assert::assertNull($container->getParameter($this->root .'.aws_api_version'));
        Assert::assertNull($container->getParameter($this->root .'.slack_client_secret'));
        Assert::assertNull($container->getParameter($this->root .'.slack_client_id'));

    }

    public function testGetConfigWithOverrideValues()
    {
        $configs = ['jws_ttl' => 5];

        $this->extension->load(array($configs), $container = $this->getContainer());

        Assert::assertEquals(5, $container->getParameter($this->root . ".jws_ttl"));
    }

    /**
     * @return StarterKitStartExtension
     */
    public function getExtension()
    {
        return new StarterKitStartExtension();
    }

    /**
     * @return ContainerBuilder
     */
    public function getContainer()
    {
        $container = new ContainerBuilder();

        return $container;
    }
}