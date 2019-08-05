#Beers

##Installation
```
composer install
yarn install
yarn build

php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console app:beers:import
```