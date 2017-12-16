# How to use Auth Tokens Stored In the database

You can see the example code for this [here](https://github.com/phptuts/starter-bundle-example/tree/database-token-example).

1) Add auth_token field to the [User](https://github.com/phptuts/starter-bundle-example/blob/database-token-example/src/AppBundle/Entity/User.php#L30) and expiration date.  Be sure to update the database.

2) You will need to extend the user service to have a findByAuthToken function or someway of finding the user by the auth token stored in the database. [Example](https://github.com/phptuts/starter-bundle-example/blob/database-token-example/src/AppBundle/Service/UserService.php#L17)

3) Create a service that implements the [AuthTokenInterface](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Service/AuthTokenServiceInterface.php).  Our service example is [here](https://github.com/phptuts/starter-bundle-example/blob/database-token-example/src/AppBundle/Service/DatabaseTokenService.php).

4) Register the [service](https://github.com/phptuts/starter-bundle-example/blob/database-token-example/app/config/services.yml#L49) and clear your cache.

```
AppBundle\Service\DatabaseTokenService:
    class: AppBundle\Service\DatabaseTokenService
    arguments:
        - '@AppBundle\Service\UserService'
        - '%app.jws_ttl%'

StarterKit\StartBundle\Service\AuthTokenServiceInterface: '@AppBundle\Service\DatabaseTokenService'

```



