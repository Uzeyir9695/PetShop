# Project Name:
Pet Shop

## Description:
This is a pet-shop API documented using Swagger.

## Installation:
- Requirements:
    - PHP version: 8.2.*
    - Laravel version: 10.0.19

- Generate OpenSSL key pairs for asymmetric key token generation by creating a "keys" directory in the project's root folder and using these commands:
    - openssl genrsa -out private.key 2048
    - openssl rsa -in private.key -outform PEM -pubout -out public.key

- Setup your database in the `.env` file and add these two variables as well:
    - JWT_TTL=604800
    - L5_SWAGGER_GENERATE_ALWAYS=true

- Swagger API documentation path:
    - '/api/v1/documentation'


- Run seeders and migration files using this command:
    - php artisan migrate:fresh --seed

- To test unit tests run command:
   - ./vendor/bin/phpunit
