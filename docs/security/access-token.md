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
This will be in the json content type.  A [CredentialTokenModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Model/Credential/CredentialTokenModel.php) will be returned.

3) The token will then be sent to the getUser method which will pass it FacebookProvider.
 
4) We validate the access token and use the facebook library to get the user's facebook user id and email address.

5) Check and see if the user exists with that facebook user id, if so return that user. If no user is found check for the email, and if that fails register the user.

6) Then [checkCredentials](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Security/Guard/GuardTrait.php#L41) is called which just returns true.  This is because token was already checked by facebook.

7) We then return a credentialed Response back to the user, in the onAuthenticationSuccess method.  If anything fails the onAuthenticationFailure function is called a 403 is returns.

```
{
	"meta": {
		"type": "authentication",
		"paginated": false
	},
	"data": {
		"user": {
			"id": "9856cf42-d862-11e7-97a4-080027192ca4",
			"displayName": null,
			"roles": [
				"ROLE_USER"
			],
			"imageUrl": null,
			"email": "glaserpower@gmail.com",
			"bio": null
		},
		"tokenModel": {
			"token": "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXUyJ9.eyJ1c2VyX2lkIjoiOTg1NmNmNDItZDg2Mi0xMWU3LTk3YTQtMDgwMDI3MTkyY2E0IiwiZXhwIjoxNTE3NTE0NDM5LCJpYXQiOjE1MTIzMzA0MzksImRpc3BsYXlOYW1lIjpudWxsLCJyb2xlcyI6WyJST0xFX1VTRVIiXSwiaW1hZ2VVcmwiOm51bGwsImVtYWlsIjoiZ2xhc2VycG93ZXJAZ21haWwuY29tIiwiYmlvIjpudWxsfQ.IQcE61WrWzgJcgFcLJLZF9vJLI4I5Zz7s-xfCnzPAUXf1xVWDI_rgzF03qzah0J86MseXpvFNyroz7BgKngbsCSyOFIzuNa8JoCtEmHPVNkAjLv__8ByInpSZN9Sdm063_LHPNSZI5_L75yZSsQHd2T1f5R2259m8ToPSsZGZhZjbJlUB8qkJysBP6FQWdSRbZbNRASFXbstCLTOrzWtiTpX5WvTMvfn70JiV9JsMP-mutADYeNOnigvW9In_o63C5NYOmC55mJyxDnE4OehsDblXbGiu3SunxAcigPBhCiN5QrmL2fH1yVQ1CW7lDJGGNXveQTabDU1pS7-A6vJgGKE9qdGwyyNgxLBqqNfjxWphNweT5Ay48yu5iWRUPUA7braWw2A-pBYeDaNNQrCb3BEN07rzEQRJOpHe-yvAn_xtcCFh9IhmUJr_Ma9MMJehIJInJWJnLUYNC91-SLifQbrSxa0g8Ls-nrdYoS_oTHsXt6VTrgK53hd3PO-bJJR-80G1hU0UD9k8fIpyE6QX-hds0w2r2m2IL0pvt9skyttMs8sWC29lrlVoWOCGkhrraatHgZ2VPYPjhb1A1PsnKPtU5hfA4XpAhfc7NVT3tAPOe4XBI7yRS3hPkB5RKLvfPZ93ZFFfLCN7EFyLm-iBvqfQI2K8zT89FK2Wpt8oH8",
			"expirationTimeStamp": 1517514439
		},
		"refreshTokenModel": {
			"token": "5f964bc41fade25d0be1302ffa63c08d7d30f5d5e2756bf6d506ed7e4c1a0fd3ed9be46aaf199da4e701e69bac9158b6bfaf9b1c73f084ff8f35bfc293be8f560b99c59b11bb89233a06541371faddb5a899893b8bec1ca800d9",
			"expirationTimeStamp": 1522698439
		}
	}
}
```


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

[Controller Method](https://github.com/phptuts/StarterBundleForSymfony/blob/src/master/Controller/SecurityController.php#L49) For Oauth:

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

This same flow is used by the refresh tokens.  Only difference is that instead of using facebook to validate the token we check our database.