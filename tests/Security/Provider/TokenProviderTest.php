<?php


namespace StarterKit\StartBundle\Security\Provider;

use Mockery\Mock;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Exception\ProgrammerException;
use StarterKit\StartBundle\Service\JWSTokenService;
use StarterKit\StartBundle\Service\UserService;
use StarterKit\StartBundle\Tests\BaseTestCase;
use StarterKit\StartBundle\Tests\Entity\User;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

class TokenProviderTest extends BaseTestCase
{
    /**
     * @var TokenProvider|Mock
     */
    protected $tokenProvider;

    /**
     * @var JWSTokenService|Mock
     */
    protected $jwsService;

    /**
     * @var UserService|Mock
     */
    protected $userService;

    public function setUp()
    {
        parent::setUp();

        $this->jwsService = \Mockery::mock(JWSTokenService::class);
        $this->userService = \Mockery::mock(UserService::class);
        $this->tokenProvider = new TokenProvider($this->jwsService, $this->userService);
    }

    /**
     * Tests happy path a valid token with a user id that is valid returns the User object
     */
    public function testValidToken()
    {
        $user = new User();
        $this->jwsService->shouldReceive('isValid')->with('token')->andReturn(true);
        $this->jwsService->shouldReceive('getUser')->with('token')->andReturn($user);
        $userFound = $this->tokenProvider->loadUserByUsername('token');

        Assert::assertEquals($user, $userFound);
    }

    /**
     * Tests that an invalid throws the UsernameNotFoundException
     */
    public function testInvalidToken()
    {
        $this->expectException(UsernameNotFoundException::class);
        $this->jwsService->shouldReceive('isValid')->with('token')->andReturn(false);

        $this->tokenProvider->loadUserByUsername('token');
    }


    /**
     * Tests that if the user id is not found in the db it throws UsernameNotFoundException
     */
    public function testUserIdNotFoundInDatabase()
    {
        $this->expectException(UsernameNotFoundException::class);
        $this->expectExceptionCode(ProgrammerException::AUTH_TOKEN_NO_USER_ID);
        $this->jwsService->shouldReceive('isValid')->with('token')->andReturn(true);
        $this->jwsService
            ->shouldReceive('getUser')
            ->with('token')
            ->once()
            ->andThrow(new ProgrammerException('bad user', ProgrammerException::AUTH_TOKEN_NO_USER_ID));

        $this->tokenProvider->loadUserByUsername('token');
    }
}