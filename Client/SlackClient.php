<?php

namespace StarterKit\StartBundle\Client;

use GuzzleHttp\Client;
use StarterKit\StartBundle\Model\User\SlackUserModel;

/**
 * Class SlackClient
 * @package StarterKit\StartBundle\Client
 */
class SlackClient
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string
     */
    protected $slackSecret;

    /**
     * @var string
     */
    protected $slackClientId;

    public function __construct(Client $client, $slackSecret, $slackClientId)
    {
        $this->client = $client;
        $this->slackSecret = $slackSecret;
        $this->slackClientId = $slackClientId;
    }

    /**
     * Returns model representing the slack user, you will need the scopes:
     * identity.basic,identity.email
     *
     * @param string $code
     * @return SlackUserModel
     */
    public function getSlackUserFromOAuthCode($code)
    {
        $params = [
            'client_id' => $this->slackClientId,
            'client_secret' => $this->slackSecret,
            'code' => $code
        ];

        $request = $this->client->request('POST', 'https://slack.com/api/oauth.access', ['form_params' => $params]);

        $data = json_decode($request->getBody()->getContents(), true);

        return new SlackUserModel(
            empty($data['user']['id']) ? null : $data['user']['id'],
            empty($data['user']['email']) ? null : $data['user']['email']
        );
    }
}