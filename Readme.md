## Installation

### Prerequisites

- Docker and Docker Compose
- Composer

### Setup

1. Clone the repository:
   ```
   https://github.com/OlexiyKh/test-api.git
   cd test-api
   ```

2. Copy the environment file:
   ```
   cp .env.example .env
   ```

3. Build and start the Docker containers:
   ```
   docker-compose up -d
   ```

4. Install dependencies:
   ```
   docker-compose exec php composer install
   ```

5. The API should now be available at `http://localhost:8080/`

## API Endpoints

### Create a Product

```
POST /products
```

Request body:
```json
{
  "name": "iPhone 14 Pro",
  "price": 999.99,
  "category": "electronics",
  "attributes": {
    "brand": "Apple",
    "color": "Space Black",
    "storage": "256GB"
  }
}
```

### Get a Product

```
GET /products/{id}
```

### Update a Product

```
PATCH /products/{id}
```

Request body (partial update):
```json
{
  "price": 899.99,
  "attributes": {
    "discount": "10%"
  }
}
```

### Delete a Product

```
DELETE /products/{id}
```

### List Products

```
GET /products
```

Optional query parameters:
- `category`: Filter by product category (e.g., "electronics")
- `minPrice`: Filter by minimum price
- `maxPrice`: Filter by maximum price

Example:
```
GET /products?category=electronics&minPrice=500&maxPrice=1000
```

## Testing

- Unit test:
```
docker-compose exec php composer test tests/Unit/Controllers/ProductControllerTest.php
```

- Integration test:
```
composer test tests/Unit/Integration/ProductApiTest.php
```
