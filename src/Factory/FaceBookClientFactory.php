<?php


namespace StarterKit\StartBundle\Factory;


use Facebook\Facebook;

/**
 * Class FaceBookClientFactory
 * @package StarterKit\StartBundle\Factory
 */
class FaceBookClientFactory implements FaceBookClientFactoryInterface
{
    /**
     * @var Facebook
     */
    protected $fb;

    /**
     * FaceBookClientFactory constructor.
     * @param string $fbAppId
     * @param string $fbAppSecret
     * @param string $fbApiVersion
     */
    public function __construct($fbAppId, $fbAppSecret, $fbApiVersion)
    {
        $this->fb = new Facebook([
            'app_id' => $fbAppId, // Replace {app-id} with your app id
            'app_secret' => $fbAppSecret,
            'default_graph_version' => $fbApiVersion,
            'http_client_handler' => 'curl'
        ]);
    }

    /**
     * @return Facebook
     */
    public function getClient()
    {
        return $this->fb;
    }
}