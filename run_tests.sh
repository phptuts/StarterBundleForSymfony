composer install --prefer-dist
php tests/Fixtures/bin/console doctrine:database:drop --env=test --force
php tests/Fixtures/bin/console doctrine:database:create --env=test
php tests/Fixtures/bin/console doctrine:schema:update --env=test --force
php tests/Fixtures/bin/console doctrine:fixtures:load --env=test --no-interaction
composer dump-autoload
vendor/bin/phpunit  --exclude-group exclude_travis
