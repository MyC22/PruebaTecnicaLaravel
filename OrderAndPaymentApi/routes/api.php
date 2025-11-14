<?php

use App\Http\Controllers\Api\Orders\OrderController;
use App\Http\Controllers\Api\Payment\PaymentController;
use Illuminate\Support\Facades\Route;

//Ruta para Ordenes
Route::prefix('orders')->group(function () {
    //Listar todos los eliminados
    Route::get('trashed', [OrderController::class, 'trashed']);

    //Listar un eliminado en especifico
    Route::get('trashed/{id}', [OrderController::class, 'showTrashed']);

    //Listar pedidos por el estado
    Route::get('pending', [OrderController::class, 'pending']);
    Route::get('paid', [OrderController::class, 'paid']);
    Route::get('failed', [OrderController::class, 'failed']);

    //Listar todo
    Route::get('/', [OrderController::class, 'index']);

    //Ruta para registrar Orden
    Route::post('register', [OrderController::class, 'store']);

    //Ruta para buscar una Orden
    Route::get('{id}', [OrderController::class, 'show']);

    //Ruta para actualizar una Orden
    Route::put('{id}', [OrderController::class, 'update']);

    //Ruta para actualizar especificamente
    Route::patch('{id}', [OrderController::class, 'update']);

    //Ruta para eliminar logicamente
    Route::delete('{id}', [OrderController::class, 'destroy']);

    //Ruta para recuperar logicamente
    Route::post('{id}/restore', [OrderController::class, 'restore']);
});


//Ruta para Payments
Route::prefix('payments')->group(function () {
    //Listar todos los eliminados
    Route::get('trashed', [PaymentController::class, 'trashed']);

    //Listar un eliminado en especifico
    Route::get('trashed/{id}/show', [PaymentController::class, 'showWithTrashed'])->name('payments.trashed.show');

    //Restaurar un eliminado
    Route::post('{id}/restore', [PaymentController::class, 'restore']);

    //Listar dependiendo del estado del pago
    Route::get('success', [PaymentController::class, 'success'])->name('payments.success');
    Route::get('failed', [PaymentController::class, 'failed'])->name('payments.failed');

    //Listar todos los pedidos
    Route::get('/', [PaymentController::class, 'index']);

    //Mostrar un solo pago
    Route::get('/{payment}', [PaymentController::class, 'show']);

    //Actualizar un pago completo o solo una parte
    Route::put('/{payment}/update', [PaymentController::class, 'update']);
    Route::patch('/{payment}/update', [PaymentController::class, 'update']);

    //Remover pago de forma logica
    Route::delete('/{payment}/delete', [PaymentController::class, 'destroy']);
});

//Pagar la orden
Route::post('orders/{order}/payments', [PaymentController::class, 'store'])->name('payments.register');
