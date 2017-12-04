# Email / Password Login

1) A json request with email and password is the sent to /login_check.

2) The request is parsed by the [getCredentials](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Guard/LoginGuard.php#L103) function and a [CredentialEmailModel]() is return.

3) The email is passed into the [EmailProvider](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Provider/EmailProvider.php) and if a user is found with that email it is return.  This happens in the getUser method.  Otherwise a UsernameNotFound Exception is thrown and the auth fails.

4) User's password is then validated in the [checkCredentials](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Guard/LoginGuard.php#L127) function.  This will return true if the password is valid.

5) The [onAuthenticationSuccess](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Guard/LoginGuard.php#L148) with a credentialed response.

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

6) If the auth fails a 403 will be returned in the [onAuthenticationFailure](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Guard/LoginGuard.php#L164) method.

Here are the security settings.  We have to register the provider and firewall.  We have a route registered as well.

[Provider](https://github.com/phptuts/starter-bundle-example/blob/master/app/config/security.yml#L12)

``` 
providers:
    email:
        id: StarterKit\StartBundle\Security\Provider\EmailProviderInterface

```

[Firewall](https://github.com/phptuts/starter-bundle-example/blob/master/app/config/security.yml#L67):

``` 
login:
    pattern: ^/login_check
    stateless: true
    provider: email
    guard:
        authenticators:
            - StarterKit\StartBundle\Security\Guard\LoginGuardInterface

```

[Controller Method](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Controller/SecurityController.php#L31) For Oauth:

``` 
/**
 *
 *  This is an example of a facebook user logging in the with a token
 *  <pre> {"type" : "facebook", "token" : "sdfasdfasdfasdf" } </pre>
 *
 *  This is an example of a user using a refresh token
 *  <pre> {"type" : "refresh_token", "token" : "sdfasdfasdfasdf" } </pre>
 *
 *  This is an example of a user logging in with email and password
 *  <pre> {"email" : "example@gmail.com", "password" : "*******" } </pre>
 *
 * @ApiDoc(
 *  resource=true,
 *  description="Api Login End Point",
 *  section="Security"
 * )
 * @Security("has_role('ROLE_USER')")
 * @Route(path="/login_check", name="_api_doc_login_check", methods={"POST"})
 *
 */
public function loginAction()
{
    throw new \LogicException("Should never hit this end point symfony should take this over.");
}
```


