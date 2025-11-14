git clone https://github.com/MyC22/PruebaTecnicaLaravel.git

composer install
npm install
npm run dev
php artisan install:api 

cp .env.example .env
php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=orderandpaymentapi
DB_USERNAME=root
DB_PASSWORD=secret


Para este proyecto se uso https://app.beeceptor.com/ debera ingresar y registrar un nombre para que se le genere una URL y al final de la url que te de agregarle /confirm
luego debera crear reglas mock:
1. /confirm
-Metodo POST 
-Request condition : Request path exactly matches
-Match value/expression: /confirm

Return HTTP status: 200
Response headers:
{
  "Content-Type": "application/json"
}

Response body: 
{
  "status": "success",
  "reference": "gw-test"
}

1. /confirm/fail
-Metodo POST 
-Request condition : Request path exactly matches
-Match value/expression: /confirm

Return HTTP status: 200
Response headers:
{
  "Content-Type": "application/json"
}

Response body: 
{
  "status": "failed",
  "reference": null
}

al crear una orden o pagar una orden o actualizar una orden o actulizar un pago en los headers debar agregar
key: Accept value:application/json
key: Content-Type  value:application/json



PAYMENT_GATEWAY_URL=https://example.com/confirm

Servidor local:
php artisan serve
php artisan migrate

Endpoints
Órdenes
Método	Ruta	Descripción
GET	/api/orders/	Listar todas las órdenes
GET	/api/orders/{id}	Ver detalles de una orden
POST	/api/orders/register	Crear una nueva orden
PUT/PATCH	/api/orders/{id}	Actualizar una orden
DELETE	/api/orders/{id}	Eliminar una orden (soft delete)
POST	/api/orders/{id}/restore	Restaurar una orden eliminada
GET	/api/orders/trashed	Listar todas las órdenes eliminadas
GET	/api/orders/trashed/{id}	Ver una orden eliminada específica
GET	/api/orders/pending	Listar órdenes con estado pending
GET	/api/orders/paid	Listar órdenes con estado paid
GET	/api/orders/failed	Listar órdenes con estado failed



Pagos
Método	Ruta	Descripción
GET	/api/payments/	Listar todos los pagos
GET	/api/payments/{payment}	Ver detalles de un pago
POST	/api/orders/{order}/payments	Crear un pago para una orden
PUT/PATCH	/api/payments/{payment}/update	Actualizar un pago (parcial o completo)
DELETE	/api/payments/{payment}/delete	Eliminar un pago (soft delete)
POST	/api/payments/{payment}/restore	Restaurar un pago eliminado
GET	/api/payments/trashed	Listar todos los pagos eliminados
GET	/api/payments/trashed/{id}/show	Ver un pago eliminado específico
GET	/api/payments/success	Listar pagos exitosos
GET	/api/payments/failed	Listar pagos fallidos



Para poder crear una orden:
{
  "customer_name": "nombre de ejemplo",
  "customer_email": "ejemplo@examples.com", 
  "customer_phone": "+51 9604552888",
  "total_amount": 505.65,
  "currency": "PEN"
}

{
    "data": {
        "id": 1,
        "customer_name": "nombre de ejemplo",
        "customer_email": "ejemplo@examples.com",
        "customer_phone": "+51 9604552888",
        "total_amount": 50565,
        "currency": "PEN",
        "status": "pending",
        "created_at": "2025-11-14T23:15:56.000000Z",
        "updated_at": "2025-11-14T23:15:56.000000Z"
    }
}


para poder pagar esa orden
{
  "payment_method": "paypal",
  "amount": 504.5
}

{
    "data": {
        "id": 1,
        "order_id": 1,
        "amount_cents": 50565,
        "amount": 505.65,
        "payment_method": "paypal",
        "status": "success",
        "attempt_number": 1,
        "external_reference": "gw-test",
        "created_at": "2025-11-14T23:10:03.000000Z",
        "deleted_at": null
    },
    "message": "Pago creado correctamente"
}



Decisiones técnicas importantes

Transacciones en la base de datos (DB::transaction)
Lo use para crear, actualizar o eliminar órdenes y pagos para garantizar que las operaciones sean seguras.


Monto en centavos (amount_cents)
Internamente los pagos se almacenan como enteros en centavos para evitar problemas de precisión con decimales en cálculos monetarios.
El cliente puede enviar 504.5 y el sistema lo convierte automáticamente a 50450 centavos.
Esto evita errores de redondeo en sumas, conversiones de divisas y reportes financieros.

Soft Deletes (SoftDeletes)
Se usa en ordenes y pagos para permitir eliminar registros de forma logica y poder restaurarlos despue
Evita perdida de datos y permite auditoría de cambios.

Uso de strings para estados y métodos de pago
Se usan campos string en lugar de enum para mayor flexibilidad.
Permite agregar nuevos estados o métodos de pago sin tener que modificar la base de datos ni hacer migraciones.

Validaciones se manejan mediante FormRequest (StorePaymentRequest, UpdatePaymentRequest) con Rule::in().
Validaciones centralizadas (FormRequest)
Todas las reglas de negocio y mensajes personalizados se definen en Requests.
Mejora la mantenibilidad y claridad del código.
Permite reutilizar las reglas en diferentes endpoints.

Tests unitarios y de feature
Se incluyen para validar lógica de negocio, reglas de validación y comportamiento de endpoints.
Unit tests: Validaciones y servicios (ej: PaymentGatewayService).
Feature tests: Endpoints y flujos completos, incluyendo errores y soft deletes.



Testing

Ejecutar todos los tests unitarios:
php artisan test --testsuite=Unit


Ejecutar tests de feature:
php artisan test --testsuite=Feature


Este proyecto está preparado para entorno local, pero se puede adaptar a producción cambiando .env.

