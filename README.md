# Project Overview

[![Build Status](https://travis-ci.org/phptuts/starterkitforsymfony.svg?branch=master)](https://travis-ci.org/phptuts/starterkitforsymfony)  [![Maintainability](https://api.codeclimate.com/v1/badges/43a21891fd78cc000fc1/maintainability)](https://codeclimate.com/github/phptuts/StarterBundleForSymfony/maintainability) [![Test Coverage](https://api.codeclimate.com/v1/badges/43a21891fd78cc000fc1/test_coverage)](https://codeclimate.com/github/phptuts/StarterBundleForSymfony/test_coverage)

## [Setup Guide]()

## Services and Interfaces

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


## No JMS Serializer, Symfony Serializer, FOS Rest Bundles

Here are some reasons we decided not to use theses. 

1) Using a serializer is a lot slower then just outputting an array
2) Using arrays and putting them in JsonResponse is way easier to test and unit test.
3) FOS Rest Bundle is confusing to configure and most projects will use json and not xml so you bias you api based on that
4) You can always add theses if u want, I think the authors have done an amazing job. ;)

## Stateless Authentication

I feel that php sessions are confusing and vary too much from version to version.  It's easier to understand authentication if every request has a token / string that represents who the user is.  I believe this also helps separate concerns in the sense that client is responsible for storing the auth token and server is responsible for validating it.  


## Ajax Login

I think it's better to do ajax login and just have the request contain a cookie that the client stores for authentication.  This means that you don't have to work about getting the last username and refreshing the page.  It's also makes  the guard logic simpler because every login response will have an auth cookie and authenticated response.


## Response Envelopes

I think that every response should be wrap around envelope that describes what it how to parse it.  The response envelops the project uses is meta, and data.  Meta will have a type that will clients to build parsers based on those types.

``` 
{
    "meta":...,
    "data":...
}
```
