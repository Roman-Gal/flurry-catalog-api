# Flurry Catalog API

A Symfony REST API for a small product catalog and menu. The project demonstrates HTTP controllers, Doctrine entities, CRUD operations and versioned database migrations.

## Features

- Product catalog with descriptions, images, quantities, tags and likes.
- Menu entries with a title, price, image and description.
- JSON CRUD endpoints backed by Doctrine ORM.
- Created and modified timestamps on both entities.
- Database schema tracked with Doctrine migrations.

## Stack

PHP 8.1+, Symfony 6.0, Doctrine ORM, MySQL/MariaDB and Composer. The repository preserves the Symfony 6 application and its locked dependencies.

## Run locally

The Symfony application is in the `flurry/` directory. Run `cd flurry` before the following commands.

1. Install the dependencies.

   ```sh
   composer install
   ```

2. Create `.env.local` and supply your local database connection and an application secret.

   ```dotenv
   APP_ENV=dev
   APP_SECRET=replace-with-a-random-local-secret
   DATABASE_URL="mysql://catalog:password@127.0.0.1:3306/catalog?serverVersion=8.0.32&charset=utf8mb4"
   ```

3. Create the database and apply the migrations.

   ```sh
   php bin/console doctrine:database:create --if-not-exists
   php bin/console doctrine:migrations:migrate --no-interaction
   ```

4. Start a local server with the Symfony CLI.

   ```sh
   symfony server:start --no-tls --port=8000
   ```

## API

| Method | Endpoint | Action |
| --- | --- | --- |
| GET | `/api/products` | List products |
| GET | `/api/products/{id}` | Read one product |
| POST | `/api/products` | Create a product |
| PUT / PATCH | `/api/products/{id}` | Update supplied product fields |
| DELETE | `/api/products/{id}` | Delete a product |
| GET | `/api/menu` | List menu entries |
| GET | `/api/menu/{id}` | Read one menu entry |
| POST | `/api/menu` | Create a menu entry |
| PUT / PATCH | `/api/menu/{id}` | Update supplied menu fields |
| DELETE | `/api/menu/{id}` | Delete a menu entry |

Send JSON with `Content-Type: application/json`. Both creation endpoints require `title` and `price`. Partial updates keep omitted fields and accept `null` for nullable fields. Empty collections and unknown identifiers return `404`.

For example, create a product using curl.

```sh
curl -X POST http://127.0.0.1:8000/api/products \
  -H "Content-Type: application/json" \
  -d '{"title":"Espresso","price":3.50,"quantity":20,"tags":"coffee"}'
```

A menu entry accepts `title`, `price`, `description` and `image`. Products additionally accept `product_image`, `product_type`, `product_description`, `quantity`, `likes` and `tags`.

## Project layout

| Path | Responsibility |
| --- | --- |
| `src/Controller/` | HTTP endpoints and request handling |
| `src/Entity/` | Product and menu data models |
| `src/Repository/` | Doctrine persistence |
| `migrations/` | Versioned database schema |
| `config/` | Symfony services, routing and database settings |
| `tests/` | PHPUnit bootstrap |

## Checks

```sh
composer validate --no-check-publish
php vendor/bin/phpunit
php bin/console lint:container
php bin/console doctrine:schema:validate
```

The PHPUnit regression checks exercise partial updates and nullable fields without a database. The schema check needs the configured database. This learning application exposes unauthenticated CRUD routes; run it with local sample data. Templates are framework scaffolding, while the implemented application interface is the JSON API.
