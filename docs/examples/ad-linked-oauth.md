#How to use Auth Tokens Stored In the database

You can see the example code for this [here](https://github.com/phptuts/starter-bundle-example/tree/github-oauth-example). Ignore stupid branch name.

1) Add linkedin user id to the [User](https://github.com/phptuts/starter-bundle-example/blob/github-oauth-example/src/AppBundle/Entity/User.php#L31) class.

2) Add a [linked in client](https://github.com/phptuts/starter-bundle-example/blob/github-oauth-example/src/AppBundle/Client/LinkedInClient.php) that will fetch take the code that linked in provides and return [OAuthUser](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Model/User/OAuthUser.php).  You will need to register this service because it will require the some config.  (client id, client secret, and redirect url). [Example](https://github.com/phptuts/starter-bundle-example/blob/github-oauth-example/app/config/services.yml#L38)

3) Create a provider that will take the register the user if can't be found by linked user id / email.  Otherwise it will update the user. [LinkedInProvider](https://github.com/phptuts/starter-bundle-example/blob/github-oauth-example/src/AppBundle/Security/Provider/LinkedInProvider.php)

4) Register the linked in [provider](https://github.com/phptuts/starter-bundle-example/blob/github-oauth-example/app/config/security.yml#L22) and [firewall](https://github.com/phptuts/starter-bundle-example/blob/github-oauth-example/app/config/security.yml#L53).