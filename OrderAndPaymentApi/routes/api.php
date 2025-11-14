<?php

use App\Http\Controllers\Api\Orders\OrderController;
use Illuminate\Support\Facades\Route;

//Listar todos los eliminados
Route::get('orders/trashed', [OrderController::class, 'trashed']);

//Listar un eliminado en especifico
Route::get('orders/trashed/{id}', [OrderController::class, 'showTrashed']);

Route::get('orders/pending', [OrderController::class, 'pending']);
Route::get('orders/paid', [OrderController::class, 'paid']);
Route::get('orders/failed', [OrderController::class, 'failed']);

//Listar todo
Route::get('orders/', [OrderController::class, 'index']);

//Ruta para registrar Orden
Route::post('orders/register', [OrderController::class, 'store']);

//Ruta para buscar una Orden
Route::get('orders/{id}', [OrderController::class, 'show']);

//Ruta para actualizar una Orden
Route::put('orders/{id}', [OrderController::class, 'update']);

//Ruta para actualizar especificamente
Route::patch('orders/{id}', [OrderController::class, 'update']);

//Ruta para eliminar logicamente
Route::delete('orders/{id}', [OrderController::class, 'destroy']);

//Ruta para recuperar logicamente
Route::post('orders/{id}/restore', [OrderController::class, 'restore']);
