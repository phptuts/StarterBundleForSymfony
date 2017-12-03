# Access Token Workflow (Facebook)

1) The browser will make a request to facebook asking for email via scopes.  The browser will get back an access token that it will then send to server.

``` 
$("#facebook_login_btn").on("click", function () {
    FB.login(function (response) {
        if (response.authResponse) {
            authenticateWithToken(response.authResponse.accessToken,
                '{{ path('_access_doc_oauth', {'provider': 'facebook'}) }}');
        } else {

        }
    }, {scope: 'email'});
});
```

2) The server will then send the access token to access-tokens/facebook with the post body: 

``` 
{'token' => 'fb_token'}
``` 
This will be in the json content type.  A [CredentialTokenModel] will be returned.

3) The token will then be sent to the getUser method which will pass it FacebookProvider.
 
4) We validate the access token and use the facebook library to get the user's facebook user id and email address.

5) Check and see if the user exists with that facebook user id, if so return that user. If no user is found check for the email, and if that fails register the user.

6) Then [checkCredentials](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Guard/GuardTrait.php#L41) is called which just returns true.  This is because token was already checked by slack.

7) We then return a credentialed Response back to the user, in the onAuthenticationSuccess method.  If anything fails the onAuthenticationFailure function is called a 403 is returns.


Here are the security settings.  We have to register the provider and firewall.  We have a route registered as well.

[Provider](https://github.com/phptuts/starter-bundle-example/blob/master/app/config/security.yml#L18)

``` 
providers:
    facebook:
        id: StarterKit\StartBundle\Security\Provider\FacebookProviderInterface

```

[Firewall](https://github.com/phptuts/starter-bundle-example/blob/master/app/config/security.yml#L51):

``` 
facebook:
    pattern: ^/access-tokens/facebook
    stateless: true
    provider: facebook
    guard:
        authenticators:
            - StarterKit\StartBundle\Security\Guard\LoginGuardInterface

```

[Controller Method](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Controller/SecurityController.php#L49) For Oauth:

``` 
/**
 * @Security("has_role('ROLE_USER')")
 * @Route(path="/access-tokens/{provider}", name="_access_doc_oauth", methods={"POST"})
 */
public function accessTokenAction()
{
    throw new \LogicException("Should never hit this end point symfony should take this over.");
}
```