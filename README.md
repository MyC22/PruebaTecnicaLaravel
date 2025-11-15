# PruebaTecnicaLaravel


## Resumen

Este proyecto es una API en Laravel para gestionar órdenes y pagos. Incluye:

* Endpoints REST para órdenes y pagos (con soft deletes)
* Validaciones con FormRequest
* Almacenamiento de montos en centavos
* Transacciones DB para operaciones críticas
* Tests unitarios y de feature

## Requisitos

* PHP >= 8.x
* Composer
* Node.js & npm
* MySQL (u otra BD compatible)

## Instalación (rápida)

Ejecuta los comandos desde la raíz del proyecto:

```bash
git clone https://github.com/MyC22/PruebaTecnicaLaravel.git
cd PruebaTecnicaLaravel
composer install
npm install
npm run dev
cp .env.example .env
php artisan key:generate
```

Configura tu `.env`

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orderandpaymentapi
DB_USERNAME=username
DB_PASSWORD=yourpassword

PAYMENT_GATEWAY_URL=https://example.com/confirm
```

Migraciones y servidor local:

```bash
php artisan migrate
php artisan serve
php artisan install:api
```

## Configurar Beeceptor

El proyecto está preparado para usar una URL de gateway externa

1. Entra a [https://app.beeceptor.com/](https://app.beeceptor.com/) y crea un mock endpoint
2. Beeceptor te dará una URL base tipo `https://mi-gateway-test.free.beeceptor.com`.
3. En tu `.env` asigna `PAYMENT_GATEWAY_URL` con esa URL y agrega `/confirm` al final, por ejemplo:

```
PAYMENT_GATEWAY_URL=https://mi-gateway-test.free.beeceptor.com/confirm
```

4. Crea reglas (mocks) en Beeceptor:

**Regla 1 — `/confirm`**

* Method: `POST`
* Request condition: `Request path exactly matches`
* Match value/expression: `/confirm`
* Return HTTP status: `200`
* Response headers:

  ```json
  { "Content-Type": "application/json" }
  ```
* Response body:

  ```json
  { "status": "success", "reference": "gw-test" }
  ```

**Regla 2 — `/confirm/fail`**

* Method: `POST`
* Request condition: `Request path exactly matches`
* Match value/expression: `/confirm/fail`
* Return HTTP status: `200`
* Response headers:

  ```json
  { "Content-Type": "application/json" }
  ```
* Response body:

  ```json
  { "status": "failed", "reference": null }
  ```

##  Endpoints principales

> Prefijo: `/api`

### Órdenes

| Método    | Ruta                       | Descripción                         |
| --------- | -------------------------- | ----------------------------------- |
| GET       | `/api/orders/`             | Listar todas las órdenes            |
| GET       | `/api/orders/{id}`         | Ver detalles de una orden           |
| POST      | `/api/orders/register`     | Crear una nueva orden               |
| PUT/PATCH | `/api/orders/{id}`         | Actualizar una orden                |
| DELETE    | `/api/orders/{id}`         | Eliminar (soft delete) una orden    |
| POST      | `/api/orders/{id}/restore` | Restaurar una orden eliminada       |
| GET       | `/api/orders/trashed`      | Listar órdenes eliminadas           |
| GET       | `/api/orders/trashed/{id}` | Ver orden eliminada específica      |
| GET       | `/api/orders/pending`      | Listar órdenes con estado `pending` |
| GET       | `/api/orders/paid`         | Listar órdenes con estado `paid`    |
| GET       | `/api/orders/failed`       | Listar órdenes con estado `failed`  |

### Pagos

| Método    | Ruta                              | Descripción                    |
| --------- | --------------------------------- | ------------------------------ |
| GET       | `/api/payments/`                  | Listar todos los pagos         |
| GET       | `/api/payments/{payment}`         | Ver detalles de un pago        |
| POST      | `/api/orders/{order}/payments`    | Crear un pago para una orden   |
| PUT/PATCH | `/api/payments/{payment}/update`  | Actualizar un pago             |
| DELETE    | `/api/payments/{payment}/delete`  | Eliminar (soft delete) un pago |
| POST      | `/api/payments/{payment}/restore` | Restaurar un pago eliminado    |
| GET       | `/api/payments/trashed`           | Listar pagos eliminados        |
| GET       | `/api/payments/trashed/{id}/show` | Ver pago eliminado específico  |
| GET       | `/api/payments/success`           | Listar pagos exitosos          |
| GET       | `/api/payments/failed`            | Listar pagos fallidos          |

## Ejemplos rápidos

**Crear una orden** (POST `/api/orders/register`)

Request JSON:

```json
{
  "customer_name": "nombre de ejemplo",
  "customer_email": "ejemplo@examples.com",
  "customer_phone": "+51 9604552888",
  "total_amount": 505.65,
  "currency": "PEN"
}
```

Respuesta (ejemplo):

```json
{
  "data": {
    "id": 1,
    "customer_name": "nombre de ejemplo",
    "customer_email": "ejemplo@examples.com",
    "customer_phone": "+51 9604552888",
    "total_amount": 50565,
    "currency": "PEN",
    "status": "pending",
    "created_at": "2025-11-14T23:15:56.000000Z"
  }
}
```

**Crear un pago** (POST `/api/orders/{order}/payments`)
Request JSON:

```json
{
  "payment_method": "paypal",
  "amount": 504.5
}
```

Respuesta (ejemplo):

```json
{
  "data": {
    "id": 1,
    "order_id": 1,
    "amount_cents": 50565,
    "amount": 505.65,
    "payment_method": "paypal",
    "status": "success",
    "external_reference": "gw-test"
  },
  "message": "Pago creado correctamente"
}
```

## Decisiones técnicas (resumen)

* **Transacciones DB** (`DB::transaction`) para operaciones compuestas (crear/actualizar/eliminar órdenes y pagos).
* **Montos en centavos** (`amount_cents`) para evitar problemas de precisión.
* **SoftDeletes** para órdenes y pagos (posibilidad de restaurar).
* **Estados y métodos como `string`** para mayor flexibilidad.
* **Validaciones via FormRequest** (`StorePaymentRequest`, `UpdatePaymentRequest`, etc.).
* **Tests**: Suite de Unit y Feature para validar comportamiento.

## Tests

Ejecutar todos los tests (Unit o Feature):

```bash
# Unit tests
php artisan test --testsuite=Unit

# Feature tests
php artisan test --testsuite=Feature
```

