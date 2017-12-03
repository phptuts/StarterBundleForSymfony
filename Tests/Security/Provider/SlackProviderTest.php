<?php


namespace StarterKit\StartBundle\Tests\Security\Provider;


use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Client\SlackClient;
use StarterKit\StartBundle\Model\User\OAuthUser;
use StarterKit\StartBundle\Security\Provider\SlackProvider;
use StarterKit\StartBundle\Service\UserService;
use StarterKit\StartBundle\Service\UserServiceInterface;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class SlackProviderTest extends BaseTestCase
{
    /**
     * @var SlackProvider
     */
    protected $slackProvider;

    /**
     * @var SlackClient|Mock
     */
    protected $slackClient;

    /**
     * @var UserServiceInterface|Mock
     */
    protected $userService;

    public function setUp()
    {
        parent::setUp();
        $this->slackClient = \Mockery::mock(SlackClient::class);
        $this->userService = \Mockery::mock(UserServiceInterface::class);
        $this->slackProvider = new SlackProvider($this->userService, $this->slackClient);
    }

    /**
     * Tests that an invalid token throws an exception
     */
    public function testInvalidToken()
    {
        $this->expectException(UsernameNotFoundException::class);
        $this->slackClient->shouldReceive('getSlackUserFromOAuthCode')
            ->with('bad_code')
            ->andReturn(new OAuthUser(null, null));
        $this->slackProvider->loadUserByUsername('bad_code');
    }

    /**
     * Tests that if a user has the slack user id that it is returned
     */
    public function testSlackUserIdAlreadyExists()
    {
        $user = new User();

        $this->slackClient->shouldReceive('getSlackUserFromOAuthCode')
            ->with('code_exists')
            ->andReturn(new OAuthUser('slack_id', 'email@gmail.com'));

        $this->userService->shouldReceive('findBySlackUserId')
                ->with('slack_id')
                ->andReturn($user);

        $returnedUser = $this->slackProvider->loadUserByUsername('code_exists');

        Assert::assertEquals($user, $returnedUser);

    }

    /**
     * Tests that if the email exists that with slack user that the id is attached to the user and is returned
     */
    public function testEmailAlreadyExists()
    {
        $user = new User();

        $this->slackClient->shouldReceive('getSlackUserFromOAuthCode')
            ->with('code_exists')
            ->andReturn(new OAuthUser('slack_id', 'email@gmail.com'));

        $this->userService->shouldReceive('findBySlackUserId')
            ->with('slack_id')
            ->andReturnNull();

        $this->userService->shouldReceive('findUserByEmail')
            ->with('email@gmail.com')
            ->andReturn($user);

        $returnedUser = $this->slackProvider->loadUserByUsername('code_exists');

        Assert::assertEquals($user, $returnedUser);
        Assert::assertEquals('slack_id', $user->getSlackUserId());

    }

    /**
     * Tests that if a new user registers with slack that the email, random password, and slack user id are saved
     */
    public function testUserSignsUpWithSlack()
    {

        $registeredUser = new User();
        $this->slackClient->shouldReceive('getSlackUserFromOAuthCode')
            ->with('code_new')
            ->andReturn(new OAuthUser('slack_id', 'email@gmail.com'));

        $this->userService->shouldReceive('findBySlackUserId')
            ->with('slack_id')
            ->andReturnNull();

        $this->userService->shouldReceive('findUserByEmail')
            ->with('email@gmail.com')
            ->andReturnNull();

        $this->userService->shouldReceive('getUserClass')
            ->andReturn(User::class);

        $this->userService->shouldReceive('registerUser')
            ->once()
            ->with(\Mockery::on(function (User $user) {
                Assert::assertNotEmpty($user->getPlainPassword());
                Assert::assertEquals('slack_id',$user->getSlackUserId());
                Assert::assertNotEmpty('email@gmail.com',$user->getEmail());

                return true;
            }), UserService::SOURCE_TYPE_SLACK)
            ->andReturn($registeredUser);


        $returnedUser = $this->slackProvider->loadUserByUsername('code_new');

        Assert::assertEquals($returnedUser, $returnedUser);


    }
}