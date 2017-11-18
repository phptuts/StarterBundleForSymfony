<?php

namespace StarterKit\StartBundle\Tests\Service\Credential;

use Mockery\Mock;
use Namshi\JOSE\SimpleJWS;
use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Exception\ProgrammerException;
use StarterKit\StartBundle\Service\JWSTokenService;
use StarterKit\StartBundle\Service\UserServiceInterface;
use StarterKit\StartBundle\Tests\Entity\User;
use Tests\BaseTestCase;

class JWSTokenServiceTest extends BaseTestCase
{
    /**
     * @var JWSTokenService
     */
    protected $jwsTokenService;

    /**
     * @var UserServiceInterface|Mock
     */
    protected $userService;

    public static $passphrase;

    public static $homeDir;

    public function setUp()
    {
        parent::setUp();
        self::$passphrase = $this->getContainer()->getParameter('starter_kit_start.jws_pass_phrase') ;
        self::$homeDir = $this->getContainer()->getParameter('kernel.project_dir');
        parent::setUp();
        $this->userService = \Mockery::mock(UserServiceInterface::class);
        $this->jwsTokenService = new JWSTokenService(
                $this->userService,
                self::$passphrase,
                $this->getContainer()->getParameter('starter_kit_start.jws_ttl'),
                self::$homeDir
            );
    }



    /**
     * Tests that the jws service can create an auth model
     */
    public function testAuthModelCreate()
    {
        $user = new User();
        $this->setObjectId($user, 15);

        $model = $this->jwsTokenService->createAuthTokenModel($user);

        $ttl = $this->getContainer()->getParameter('starter_kit_start.jws_ttl');

        $lessThanExpirationTimeStamp = (new \DateTime())->modify('+' . $ttl - 500 .  ' seconds')->getTimestamp();
        $greaterThanExpirationTimeStamp = (new \DateTime())->modify('+' . ($ttl + 500) .  ' seconds')->getTimestamp();

        Assert::assertTrue($lessThanExpirationTimeStamp < $model->getExpirationTimeStamp());
        Assert::assertTrue($greaterThanExpirationTimeStamp > $model->getExpirationTimeStamp());
        Assert::assertTrue($this->jwsTokenService->isValid($model->getToken()));
        $payload = $this->jwsTokenService->getPayload($model->getToken());
        Assert::assertEquals(15, $payload['user_id']);
        Assert::assertEquals($model->getExpirationTimeStamp(), $payload['exp']);
        Assert::assertArrayHasKey('iat', $payload);
    }

    /**
     * Tests that a bad token when reading the payload will throw an exception.
     */
    public function testInvalidTokenThrowsProgrammerException()
    {
        $this->expectException(ProgrammerException::class);
        $this->expectExceptionCode(ProgrammerException::JWS_INVALID_TOKEN_FORMAT);
        $this->expectExceptionMessage('Unable to read jws token.');
        $this->jwsTokenService->getPayload('token');
    }

    /**
     * Asserts that bad tokens are not valid
     * @dataProvider badTokens
     * @param $token
     */
    public function testInvalidToken($token)
    {
        Assert::assertFalse($this->jwsTokenService->isValid($token));
    }

    /**
     * Tests that if there is no user id in the token the proper exception is thrown
     */
    public function testTokenWithNoUserId()
    {
        $this->expectException(ProgrammerException::class);
        $this->expectExceptionCode(ProgrammerException::AUTH_TOKEN_NO_USER_ID);
        $user = new User();
        $this->setObjectId($user, 15);
        $token = self::createTokenWithOutUserId($user);
        $this->jwsTokenService->getUser($token);
    }

    /**
     * Tests that if there is no user id in the token the proper exception is thrown
     */
    public function testTokenWhereUserCanNotBeFound()
    {
        $this->expectException(ProgrammerException::class);
        $this->expectExceptionCode(ProgrammerException::AUTH_TOKEN_NO_USER_WITH_ID_FOUND);
        $user = new User();
        $this->setObjectId($user, 15);
        $token = self::createExpiredToken($user);
        $this->userService->shouldReceive('findUserById')->with(15)->once()->andReturnNull();
        $this->jwsTokenService->getUser($token);
    }

    /**
     * Tests that if a user is found with the token id that it is return
     */
    public function testTokenWithValidUserId()
    {
        $user = new User();
        $this->setObjectId($user, 15);
        $token = self::createExpiredToken($user);
        $this->userService->shouldReceive('findUserById')->with(15)->once()->andReturn($user);
        $actualUser = $this->jwsTokenService->getUser($token);
        Assert::assertEquals($user, $actualUser);
    }

    /**
     * A data provider for bad tokens
     * @return array
     */
    public function badTokens()
    {
        return [
            ['token'],
            ['eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiYWRtaW4iOnRydWV9.TJVA95OrM7E2cBab30RMHrHDcEfxjoYZgeFONFh7HgQ']
        ];
    }

    /**
     * Tests that expired tokens are not valid
     */
    public function testExpiredToken()
    {
        $user = new User();
        $this->setObjectId($user, 15);
        $expiredToken = self::createExpiredToken($user);

        Assert::assertFalse($this->jwsTokenService->isValid($expiredToken));
    }

    /**
     * @param User $user
     * @return string
     */
    public static function createExpiredToken(User $user)
    {
        $privateKey = openssl_pkey_get_private(file_get_contents(self::$homeDir . '/var/jwt/private.pem'), self::$passphrase);
        $jws = new SimpleJWS([
            'alg' => 'RS256'
        ]);

        $expirationDate = new \DateTime();
        $expirationDate->modify('-10 seconds');
        $expirationTimestamp = $expirationDate->getTimestamp();

        $jws->setPayload([
            'user_id' => $user->getId(),
            'exp' => $expirationTimestamp,
            'iat' => (new \DateTime())->getTimestamp()
        ]);

        $jws->sign($privateKey);

        return $jws->getTokenString();
    }

        /**
         * @param User $user
         * @return string
         */
    public static function createTokenWithOutUserId(User $user)
    {
        $privateKey = openssl_pkey_get_private(file_get_contents(self::$homeDir . '/var/jwt/private.pem'), self::$passphrase);
        $jws = new SimpleJWS([
            'alg' => 'RS256'
        ]);

        $expirationDate = new \DateTime();
        $expirationDate->modify('-10 seconds');
        $expirationTimestamp = $expirationDate->getTimestamp();

        $jws->setPayload([
            'exp' => $expirationTimestamp,
            'iat' => (new \DateTime())->getTimestamp()
        ]);

        $jws->sign($privateKey);

        return $jws->getTokenString();
    }

}