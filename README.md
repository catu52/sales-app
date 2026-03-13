# Sales app challenge

API to manage sales for a company that markets products and services.

## Requirements

- PHP 8.0+
- MySQL v8.0+
- Composer

**Recommended:**
- Docker

## Installation

The recommended way to run the project is by using the provided **devcontainer**

Install composer dependencies:

`$ composer install`

Run migrations with seeders to have some nice dummy data and generate Sanctum token for testing purposes:

`$ php artisan migrate --seed`

### API endpoints

    GET|HEAD        api/v1/clients 
    POST            api/v1/clients
    GET|HEAD        api/v1/clients/{client}
    PUT|PATCH       api/v1/clients/{client}
    DELETE          api/v1/clients/{client}
    GET|HEAD        api/v1/items
    DELETE          api/v1/items/{id}
    POST            api/v1/products
    POST            api/v1/sales
    GET|HEAD        api/v1/sales
    GET|HEAD        api/v1/sales/{id}