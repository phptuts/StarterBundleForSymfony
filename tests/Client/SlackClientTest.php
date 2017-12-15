<?php

namespace StarterKit\StartBundle\Tests\Client;

use GuzzleHttp\Client;
use Mockery\Mock;
use PHPUnit\Framework\Assert;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use StarterKit\StartBundle\Client\SlackClient;
use StarterKit\StartBundle\Tests\BaseTestCase;

class SlackClientTest extends BaseTestCase
{
    /**
     * @var Client|Mock
     */
    protected $client;

    /**
     * @var SlackClient
     */
    protected $slackClient;

    public function setUp()
    {
        parent::setUp();
        $this->client = \Mockery::mock(Client::class);
        $this->slackClient = new SlackClient($this->client, 'secret', 'client_id');
    }

    public function testCodeExchangeForUser()
    {
        $jsonString = '{
                "ok": true,
                "access_token": "fake_access_token",
                "scope": "identity.basic,identity.email",
                "user": {
                    "name": "Noah Glaser",
                    "id": "fake_user_id",
                    "email": "fake_email@gmail.com"
                },
                "team": {
                    "id": "team_id"
                }
            }';


        $streamInterface = \Mockery::mock(StreamInterface::class);
        $streamInterface->shouldReceive('getContents')->andReturn($jsonString);

        $response = \Mockery::mock(ResponseInterface::class);
        $response->shouldReceive('getBody')->andReturn($streamInterface);


        $this->client->shouldReceive('request')->with('POST', 'https://slack.com/api/oauth.access', [
            'form_params' => [
                'client_id' => 'client_id',
                'client_secret' => 'secret',
                'code' => 'code'
            ]
        ])->andReturn($response);

        $slackModel = $this->slackClient->getSlackUserFromOAuthCode('code');

        Assert::assertEquals('fake_email@gmail.com', $slackModel->getEmail());
        Assert::assertEquals('fake_user_id', $slackModel->getUserId());
    }
}