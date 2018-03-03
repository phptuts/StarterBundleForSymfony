# User Entity

The user entity implements traits which allow you to decide which features you may or may not want to implement.  Say you only have facebook login, you would only implement the [FacebookTrait](https://github.com/phptuts/StarterBundleForSymfony/blob/8de076eaa1d98ae8e1887ce61bced5672c307838/src/Entity/FacebookTrait.php) on the entity.  This way your user table does not have to grow with this project.  Say thing applies for images as well.

## [ImageTrait](https://github.com/phptuts/StarterBundleForSymfony/blob/8de076eaa1d98ae8e1887ce61bced5672c307838/src/Entity/ImageTrait.php)

The image trait will store the vendor, url, and id.  The idea behind this is that you might want to switch vendor or move slowly to another vendor.  This allows you to do that without worrying about which file was uploaded to which service.