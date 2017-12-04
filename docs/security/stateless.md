# Stateless Authentication Guards

1) Look for the auth token in cookie or the Authorization header. If one is found return [CredentialTokenModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Model/Credential/CredentialTokenModel.php).  If one is not found for the website it will redirect the user to the login screen with next_url="where the request came from".  This redirect happens in the [start](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Guard/StateLess/WebsiteGuard.php#L70) method.  If it's the api it will return a 401.

2) We use the auth service to check it's validity and fetch the user.  By default that will [JWSTokenService](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Service/JWSTokenService.php).  The provider is the [TokenProvider](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Provider/TokenProvider.php)

3) checkCredential will return true because it has already been validated by the provider.

4) onAuthenticationSuccess returns null which will allow the request to continue.

5) If auth fails for whatever reason it will go to onAuthenticationFailure.  On the web that will redirect the user back to the login screen.

You can check out the example configuration for this [here](https://github.com/phptuts/starter-bundle-example/blob/master/app/config/security.yml#L75):



