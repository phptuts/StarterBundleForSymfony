# Setup Guide and Project Overview

[![Build Status](https://travis-ci.org/phptuts/starterkitforsymfony.svg?branch=master)](https://travis-ci.org/phptuts/starterkitforsymfony)  

[![Maintainability](https://api.codeclimate.com/v1/badges/43a21891fd78cc000fc1/maintainability)](https://codeclimate.com/github/phptuts/StarterBundleForSymfony/maintainability)
 
[![Test Coverage](https://api.codeclimate.com/v1/badges/43a21891fd78cc000fc1/test_coverage)](https://codeclimate.com/github/phptuts/StarterBundleForSymfony/test_coverage)

## Setup Guide Symfony 3 / 4
 

1) Install the bundle
``` 
composer require start-kit-symfony/start-bundle
```

2) Add to Bundle class to the app kernel, only need if you are using symfony 3.

``` 
    new StarterKit\StartBundle\StarterKitStartBundle(),
```

3) cd into the directory where your project is
4) Create a jwt directory in your var folder
``` 
mkdir var/jwt
```
5) Create your private key with and write down the pass phrase you used.

``` 
openssl genrsa -out var/jwt/private.pem -aes256 4096
```
6) Create your public key, you will need the pass phrase here and in the composer install step

``` 
openssl rsa -pubout -in var/jwt/private.pem -out var/jwt/public.pem
```

7) In your AppBundle -> Entity folder create a User class that extends the [BaseUser](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Entity/BaseUser.php).

8) When u create your s3 Bucket you will need to a folder for each environment you have. In that folder you will need to add another folder called profile_pics which is where the personal pictures are stored. Say you have dev and prod.  You can over ride this or not use s3 if you want to.

    prod -> profile_pics
    dev -> profile_pics

9) Configure the Bundle, in the app -> config -> config.yml file for symfony 3 and for symfony for create a file called starter_kit_start.yaml in config -> packages.  They are both yaml files.

``` 
starter_kit_start:

    login_url: '%app.login_url%' # this is the path that your login screen is.  This where website guard will nagivate people if login is required and the user is not logged in.

    jws_ttl: '%app.jws_ttl%' # This the number of seconds the jwt token will live
    jws_pass_phrase: '%app.jws_pass_phrase%' # This the pass phrased you used to create jwt private / public keys.
    refresh_token_ttl: '%app.refresh_token_ttl%' # This how long the refresh token will live.

    user_class: '%app.user_class%' # This is concrete class that extends the base user

    facebook_app_secret: '%app.facebook_app_secret%' # This is client secret that you get when you register your website with facebook
    facebook_api_version: '%app.facebook_api_version%' # Facebook Api Version
    facebook_app_id: '%app.facebook_app_id%' # This is your facebook app id

    google_client_id: '%app.google_client_id%' # This is your google client id
 

    # All this information is found when you create the bucket
    aws_api_version: '%app.aws_api_version%' 
    aws_key: '%app.aws_key%'
    aws_secret: '%app.aws_secret%'
    aws_region: '%app.aws_region%' 
    aws_s3_bucket_name: '%app.aws_region%'

    # This client secret / client are found when u register your app with slack
    slack_client_secret: '%app.slack_client_secret%'
    slack_client_id: '%app.slack_client_id%'

```

10) Register Firewalls and Security Providers. This will be in the app -> config -> security.yml for symfony 3 and in config -> packages -> security.yaml for symfony 4.


``` 
security:

    encoders:
        AppBundle\Entity\User:
            algorithm: bcrypt
            cost: 12

    # https://symfony.com/doc/current/security.html#b-configuring-how-users-are-loaded
    providers:
        email:
            id: StarterKit\StartBundle\Security\Provider\EmailProviderInterface
        slack:
            id: StarterKit\StartBundle\Security\Provider\SlackProviderInterface
        token:
            id: StarterKit\StartBundle\Security\Provider\TokenProviderInterface
        facebook:
            id: StarterKit\StartBundle\Security\Provider\FacebookProviderInterface
        google:
            id: StarterKit\StartBundle\Security\Provider\GoogleProviderInterface
        refresh:
            id: StarterKit\StartBundle\Security\Provider\RefreshTokenProviderInterface

    role_hierarchy:
        ROLE_ADMIN:  [ROLE_USER, ROLE_ALLOWED_TO_SWITCH]


    firewalls:
        # disables authentication for assets and the profiler, adapt it according to your needs
        dev:
            pattern: ^/(_(profiler|wdt)|css|images|js)/
            security: false

        facebook:
            pattern: ^/access-tokens/facebook
            stateless: true
            provider: facebook
            guard:
                authenticators:
                    - StarterKit\StartBundle\Security\Guard\LoginGuardInterface

        google:
            pattern: ^/access-tokens/google
            stateless: true
            provider: google
            guard:
                authenticators:
                    - StarterKit\StartBundle\Security\Guard\LoginGuardInterface

        slack:
            pattern: ^/oauth/slack*
            stateless: true
            provider: slack
            guard:
                authenticators:
                    - StarterKit\StartBundle\Security\Guard\OAuthGuardInterface

        refresh:
            pattern: ^/access-tokens/refresh
            stateless: true
            provider: refresh
            guard:
                authenticators:
                    - StarterKit\StartBundle\Security\Guard\LoginGuardInterface

        login:
            pattern: ^/login_check
            stateless: true
            provider: email
            guard:
                authenticators:
                    - StarterKit\StartBundle\Security\Guard\LoginGuardInterface

        api:
            pattern: ^/api*
            anonymous: ~
            stateless: true
            provider: token
            guard:
                authenticators:
                    - StarterKit\StartBundle\Security\Guard\StateLess\ApiGuardInterface

        main:
            pattern: ^/*
            anonymous: ~
            provider: token
            stateless: true
            guard:
                authenticators:
                    - StarterKit\StartBundle\Security\Guard\StateLess\WebsiteGuardInterface

    access_control:
        - { path: ^/admin, roles: ROLE_ADMIN }

```


## Project Overview

### Services and Interfaces

Every service has an interface that is registered as a service.  This bundle only uses interfaces in the constructor of the classes.  This means that all you have to do to over ride a service is find the interface it is implementing and register the interface as a service in the app bundle.  

Here is an example.  Say you wanted to use tokens stored in the database instead of jwt / jws tokens.  All you would have to do is create a service that implemented the [AuthTokenServiceInterface](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Service/AuthResponseServiceInterface.php) and register it in the app bundle.  

#### [Service Registration](https://github.com/phptuts/starter-bundle-example/blob/database-token-example/app/config/services.yml#L49) 

``` 
AppBundle\Service\DatabaseTokenService:
    class: AppBundle\Service\DatabaseTokenService
    arguments:
        - '@AppBundle\Service\UserService'
        - '%app.jws_ttl%'

StarterKit\StartBundle\Service\AuthTokenServiceInterface: '@AppBundle\Service\DatabaseTokenService'

```
You can find the actual class implementation [here](https://github.com/phptuts/starter-bundle-example/blob/database-token-example/src/AppBundle/Service/DatabaseTokenService.php).

Here is where services are registered for the bundle. [services.yml](https://github.com/phptuts/StarterBundleForSymfony/blob/master/Resources/config/services.yml)


### No JMS Serializer, Symfony Serializer, FOS Rest Bundles

Here are some reasons we decided not to use theses. 

1) Using a serializer is a lot slower then just outputting an array
2) Using arrays and putting them in JsonResponse is way easier to test and unit test.
3) FOS Rest Bundle is confusing to configure and most projects will use json and not xml so you bias you api based on that
4) You can always add theses if u want, I think the authors have done an amazing job. ;)

### Stateless Authentication

I feel that php sessions are confusing and vary too much from version to version.  It's easier to understand authentication if every request has a token / string that represents who the user is.  I believe this also helps separate concerns in the sense that client is responsible for storing the auth token and server is responsible for validating it.  


### Ajax Login

I think it's better to do ajax login and just have the request contain a cookie that the client stores for authentication.  This means that you don't have to work about getting the last username and refreshing the page.  It's also makes  the guard logic simpler because every login response will have an auth cookie and authenticated response.


### Response Envelopes

I think that every response should be wrap around envelope that describes what it how to parse it.  The response envelops the project uses is meta, and data.  Meta will have a type that will clients to build parsers based on those types.

``` 
{
    "meta":...,
    "data":...
}
```

### Email Only Login

I feel that email are the best approach to login and not username.  Mainly because they are unique and allow user tables to merged on a single point.  

## Table of Contents

- [Services](docs/services.md)
- [Response / Serialization](docs/serialize-response.md)
- [Forms](docs/forms.md)
- [Security](docs/security.md)    
    
## How To / Examples

- [How to use Auth Tokens Stored In the database](docs/examples/auth-db-tokens.md)
- [How to add Linked In Login (OAuth Provider)](docs/examples/ad-linked-oauth.md)
- [How to add EasyAdmin Bundle For User Management](docs/examples/easy-admin.md)
- [How to log the user in after they have registered](docs/examples/register-login.md)
- [How to send an email after the user registers](docs/example/register-email.md)
