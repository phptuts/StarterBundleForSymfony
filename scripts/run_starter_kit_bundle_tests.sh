#!/usr/bin/env bash

php bin/console doctrine:database:drop --env=starter_kit_test --force
php bin/console doctrine:database:create --env=starter_kit_test
php bin/console doctrine:schema:update --env=starter_kit_test --force
php bin/console doctrine:fixtures:load --env=starter_kit_test --no-interaction --fixtures=vendor/start-kit-symfony/start-bundle/DataFixtures/ORM/
bin/phpunit -c vendor/start-kit-symfony/start-bundle/ --coverage-html=reports --exclude-group exclude_travis