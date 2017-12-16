# Forms

All forms have csrf protection disabled by default.  This done so that the api controllers can use them.  Also all the validation for these forms are found on the [BaseUser](https://github.com/phptuts/StarterBundleForSymfony/blob/src/master/Entity/BaseUser.php) and are done via annotations.

## [Forget Password](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Form/ForgetPasswordType.php)

This form has a transformer on the whole form.  So if it can not find the email address it will return a form error and will put that on the email field.

## [Update User](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Form/UpdateUserType.php)

The important thing to note with this form that we don't allow api to upload the image.  This is disabled by passing the form option api => true.

## [Change Password](https://github.com/phptuts/StarterBundleForSymfony/blob/master/src/Form/ChangePasswordType.php)

ROLE_ADMIN is not required to enter current user's password.  This allows for them to change the password without knowing the current user's password.  This would come in handy if you were building a customer service tool.