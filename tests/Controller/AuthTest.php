<?php

namespace StarterKit\StartBundle\Tests\Controller;

use PHPUnit\Framework\Assert;
use StarterKit\StartBundle\Entity\BaseUser;
use StarterKit\StartBundle\Service\AuthResponseService;
use StarterKit\StartBundle\Tests\Service\Credential\JWSTokenServiceTest;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends BaseApiTestCase
{
    /**
     * The auth test email
     * @var string
     */
    const TEST_EMAIL = 'glaserpower+register_test@gmail.com';

    /**
     * Tests that registration validation works
     */
    public function testRegisterValidation()
    {
        $client = $this->makeClient();
        $response = $this->makeJsonRequest($client, Request::METHOD_POST, '/api/users', []);
        $json = $this->getJsonResponse($response);

        Assert::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());

        Assert::assertNotEmpty($json['data']['children']['email']['errors']);
        Assert::assertNotEmpty($json['data']['children']['plainPassword']['errors']);
        Assert::assertEquals('formErrors',$json['meta']['type']);
        Assert::assertFalse($json['meta']['paginated']);
    }

    /**
     * Tests that registration works and that the credential response is valid
     */
    public function testRegister()
    {
        $client = $this->makeClient();
        $response = $this->makeJsonRequest(
            $client,
            Request::METHOD_POST,
            '/api/users',
            ['email' => self::TEST_EMAIL, 'plainPassword' => 'password']
        );

        $this->assertCredentialsResponse($response, $client, self::TEST_EMAIL);

        return $this->getJsonResponse($response)['data']['refreshTokenModel']['token'];


    }

    /**
     * Test the new user can login with api
     *
     * @depends testRegister
     */
    public function testApiLoginEmailAndPassword()
    {
        $client = $this->makeClient();
        $response = $this->makeJsonRequest(
            $client,
            Request::METHOD_POST,
            '/login_check',
            ['email' => self::TEST_EMAIL, 'password' => 'password']
        );

        $this->assertCredentialsResponse($response, $client, self::TEST_EMAIL);
    }

    /**
     * Tests that invalid credential returns a 403 response.
     */
    public function testApiInvalidCredentials()
    {
        $client = $this->makeClient();
        $response = $this->makeJsonRequest(
            $client,
            Request::METHOD_POST,
            '/login_check',
            ['email' => 'adsfasdfasdfa', 'password' => 'password']
        );

        Assert::assertEquals(Response::HTTP_FORBIDDEN, $response->getStatusCode());
    }

    /**
     * Test that a user can use a refresh token to login through the api
     *
     * @depends testRegister
     * @param $refreshToken
     */
    public function testRefreshTokenLogin($refreshToken)
    {
        $client = $this->makeClient();
        $response = $this->makeJsonRequest(
            $client,
            Request::METHOD_POST,
            '/access-tokens/refresh',
            ['token' => $refreshToken]
        );

        $this->assertCredentialsResponse($response, $client, self::TEST_EMAIL);
    }

    /**
     * Tests that if a user has an expired jwt token on the website that they are redirected to the login page
     */
    public function testExpiredJWTTokenRedirectToLoginPageOnce()
    {
        $user = $this->userRepository->findByEmail('example@gmail.com');
        JWSTokenServiceTest::$homeDir = $this->getContainer()->getParameter('kernel.project_dir');
        JWSTokenServiceTest::$passphrase = $this->getContainer()->getParameter('starter_kit_start.jws_pass_phrase');
        $expiredJWTToken = JWSTokenServiceTest::createExpiredToken($user);
        $client = $this->makeClient();
        $expiredCookieDateTime = (new \DateTime())->modify('+10 days');
        $client->getCookieJar()->set(new Cookie(
            AuthResponseService::AUTH_COOKIE,
            $expiredJWTToken,
            $expiredCookieDateTime->getTimestamp()
            )
        );
        $client->request(Request::METHOD_GET, '/test_homepage');
        $crawler = $client->followRedirect();
        $response = $client->getResponse();
        Assert::assertFalse($response->headers->has(AuthResponseService::AUTH_COOKIE));
        Assert::assertContains('/login?next_url=/test_homepage', $crawler->getBaseHref());
    }

    /**
     * This is excluded from travis ci because it involves a secret
     *
     * @group exclude_travis
     * Tests that a facebook user can login
     */
    public function testFacebookLogin()
    {
        $facebookAuthToken = $this->getFacebookAuthTokenAndEmail();

        $client = $this->makeClient();
        $response = $this->makeJsonRequest(
            $client,
            Request::METHOD_POST,
            '/access-tokens/facebook',
            ['token' => $facebookAuthToken['token']]
        );

        $this->assertCredentialsResponse($response, $client, $facebookAuthToken['email']);

        $user = $this->userRepository->findByEmail($facebookAuthToken['email']);

        Assert::assertInstanceOf(BaseUser::class, $user);
        // Tests that the facebook user id is not empty
        Assert::assertNotEmpty($user->getFacebookUserId());

    }
}