#How to add EasyAdmin Bundle For User Management

1) Set up the config for the user entity.

``` 
easy_admin:
    entities:
        Users:
            class: AppBundle\Entity\User
            disabled_actions: ['delete']
            form:
                fields:
                    - displayName
                    - email
                    - { property: 'plainPassword', label: 'Password', type: 'password'}
                    - { property: 'roles', type: 'choice', type_options: { multiple: true, choices: { 'ROLE_USER': 'ROLE_USER', 'ROLE_ADMIN': 'ROLE_ADMIN' } } }

            list:
                fields: ['email', 'displayName', 'roles', 'enabled', 'source']
                actions:
                    - { name: 'edit', icon: 'pencil', label: '' }
    site_name: 'Admin'
```

2) Create a [AdminController](https://github.com/phptuts/starter-bundle-example/blob/master/src/AppBundle/Controller/Admin/AdminController.php) and hook into the events to make sure the user's password get's  saved right.


