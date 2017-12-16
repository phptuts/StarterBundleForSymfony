# How to send an email after the user registers

So every time a user register an event is fired called register_event.  This is done in the [UserService](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Service/UserService.php#L270).

All you have to do is [register an a service](https://github.com/phptuts/starter-bundle-example/blob/master/app/config/services.yml#L48) and will take in a [UserEvent](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Event/UserEvent.php).  Here is the example [service](https://github.com/phptuts/starter-bundle-example/blob/master/src/AppBundle/Listener/UserListener.php).