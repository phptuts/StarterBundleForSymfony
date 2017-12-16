#Security

## Guards

We use guards to security the platform guards.  A guard is a class that implements AuthenticatorInterface.  None of our guards support remember me because all guards are stateless in our system.  We don't rely on sessions.

### Guard Methods that are important

- supports:
    Used to determine if the guard supports the request being sent.  Returns a boolean.

- getCredentials: 

    Return the what is need to validate whether the response is a valid user.  Often called the user credentials.
    
- getUser

    This function returns a user object based on what is return from the getCredentials function.  This function uses a user provider to fetch the user.
    
- checkCredentials

    This function validates whether the user and auth information is valid.  For tokens this is done in the user provider and will always return true.
    
- onAuthenticationSuccess

    This function returns an authenticated response or null.  If the function returns null it will pass the request through.  This is done in our stateless guards.
    
- onAuthenticationFailure 
    
    This function is called when the authenticated request fails.
    
- start
    
    This function is called when the authentication is required but no one is there.
    
## Providers

These take the credentials return and return a user. For some that means just looking up the email, for others like facebook it means that http requests will need to made in order to validate token provided.

## CredentialModels / [CredentialInterface](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Model/Credential/CredentialInterface.php)

These were created as a standard way of passing data to the getUser function.  The getUserIdentifier() function contains the information needed to look for the user.  For example [CredentialEmailModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Model/Credential/CredentialEmailModel.php) will pass the email through this function, while the [CredentialTokenModel](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Model/Credential/CredentialTokenModel.php) will pass the token it received.

## [UserVoter](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Security/Voter/UserVoter.php)

We have one voter called the user voter this. This is used to allow admin / or the user that login to edit itself.  These are used in the controller, here is an example below.

``` 
$this->denyAccessUnlessGranted(UserVoter::USER_CAN_VIEW_EDIT, $user);
```

## Work Flows

- [OAuth](security/oauth.md) 
- [Access Token](security/access-token.md) 
- [Email / Password](security/email-password.md) 
- [StateLess Login (Website / Api)](security/email-password.md)

