#Security

## Guards

We use guards to security the platform guards.  A guard is a class that implements GuardAuthenticatorInterface.  None of our guards support remember me because all guards are stateless in our system.  We don't rely on sessions.

### Guard Methods that are important

- getCredentials: 

    Used to get information from the request to validate if it has something validate.  If the function returns null the request is fed into the start function if login is required.
    
- getUser

    This function returns a user object based on what is return from the getCredentials function.  This function uses a user provider to fetch the user.
    
- checkCredentials

    This Function validates whether the user and auth information is valid.  For tokens this is done in the user provider.
    
- onAuthenticationSuccess

    This function returns an authenticated response or null.  If the function returns null it will pass the request through.  This is done in our stateless guards.
    
- onAuthenticationFailure 
    
    This function is called when the authenticated request fails.
    
- start
    
    This function is called when the authentication is required but no one is there.
    
## Providers

These take the credentials return and return a user. For some that means just looking up the email, for others like facebook it means that http requests will need to made in order to validate token provided.

## CredentialModels / [CredentialInterface](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Model/Credential/CredentialInterface.php)

These were created as a standard way of passing data to the getUser Function.  The getUserIdentifier() function contains the information needed to look for the user.  For example [CredentialEmailModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Model/Credential/CredentialEmailModel.php) will pass the email through this function, while the [CredentialTokenModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Model/Credential/CredentialTokenModel.php) will pass the token it received.

## [UserVoter](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Security/Voter/UserVoter.php)

We have one voter called the user voter this. This is used to allow admin / or the user that login to edit itself.  These are used in the controller, here is an example below.

``` 
$this->denyAccessUnlessGranted(UserVoter::USER_CAN_VIEW_EDIT, $user);
```

## Work Flows

- [OAuth](security/oauth.md) 
- [Access Token](security/access-token.md) 
- [Email / Password](security/email-password.md) 
- [StateLess Login (Website / Api)](security/email-password.md)

