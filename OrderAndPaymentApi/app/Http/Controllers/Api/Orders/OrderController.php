<?php

namespace App\Http\Controllers\Api\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{

    /*Listar*/
    public function index(Request $request)
    {
        $orders = Order::withCount('payments')
        ->with('payments')
        ->orderBy('created_at', 'desc')
        ->paginate(10);

        return OrderResource::collection($orders);
    }

    /* Registrar Orden */
    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Usar transacción por si se amplia el flujo
        $order = DB::transaction(function () use ($data) {
            return Order::create([
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'total_amount' => $data['total_amount'],
                'currency' => $data['currency'] ?? 'PEN',
                'status' => 'pending',
            ]);
        });

        return (new OrderResource($order))->response()->setStatusCode(201);
    }

    /*Buscar Orden por ID*/
    public function show($id):JsonResponse
    {
        $order = Order::with('payments')->find($id);
        if (!$order) {
            return response()->json(['message' => 'Orden no encontrada'], 404);
        }

        return response()->json(new OrderResource($order), 200);
    }

    /*Actualizar*/
    public function update(UpdateOrderRequest $request, $id): JsonResponse
    {
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrado'],404);
        }

        $data = $request->validated();

        $updated = DB::transaction(function () use ($order, $data){
            $order->fill($data);

            if (!$order->isDirty()) {
                return null;
            }

            $order->save();
            return $order->fresh();
        });

        if ($updated == null) {
            return response()->json(['message' => 'No se realizaron cambios porque los datos son iguales'
                                    ,'order' => new OrderResource($order)], 200);
        }

        return response()->json(['message' => 'Pedido actualizado correctamente',
                                 'order' => new OrderResource($updated)], 200);
    }

    /*Remover logicamente pero no fisicamente */
    public function destroy($id): JsonResponse
    {
        $order = Order::find($id);

        if (!$order) {
            return response()->json(['message' => 'Pedido no encontrado'],404);
        }

        DB::transaction(function () use ($order) {
            $order->delete();
        });

        return response()->json(['message' => 'Pedido eliminado correctamente',
                                 'order_id' => $order->id,
                                 'deleted_at' => $order->delete_at,], 200);
    }

    /*Restaurar Orden Eliminada */
    public function restore($id): JsonResponse
    {
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return response()->json(['message' => 'Orden no encontrado'],404);
        }

        if (!$order->trashed()) {
            return response()->json(['message' => 'Esta Orden no esta eliminada'],422);
        }

        DB::transaction(function () use ($order){
            $order->restore();
        });

        return response()->json(['message' => 'Pedido restaurado correctamente',
                                 'order' => new OrderResource($order->fresh())], 200);
    }

    /*Listar ordenes eliminadas */
    public function trashed(Request $request)
    {
        $query = Order::onlyTrashed()->with('payments')->orderBy('deleted_at', 'desc');

        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }

        $orders = $query->paginate(10);

        if ($orders->isEmpty()) {
            return response()->json(['message' => 'No se encontraron ordenes eliminadas'], 200);
        }

        return OrderResource::collection($orders);
    }

    /*Mostrar orden elimiada especifica */
    public function showTrashed($id)
    {
        $order = Order::withTrashed()->find($id);

        if (!$order) {
            return response()->json(['message' => 'Orden eliminada no encontrado'],404);
        }

        return response()->json(new OrderResource($order), 200);
    }

    /*Listar ordenes con estado pending */
    public function pending(Request $request)
    {
        $query = Order::where('status', 'pending')
            ->withCount('payments')
            ->with('payments')
            ->orderBy('created_at', 'desc');

        $orders = $query->paginate(10);
        return OrderResource::collection($orders);
    }

    /*Listar ordenes con estado paid */
    public function paid(Request $request)
    {
        $query = Order::where('status', 'paid')
            ->withCount('payments')
            ->with('payments')
            ->orderBy('created_at', 'desc');

        $orders = $query->paginate(10);
        return OrderResource::collection($orders);
    }

    /*Listar ordenes con estado failed */
    public function failed(Request $request)
    {
        $query = Order::where('status', 'failed')
            ->withCount('payments')
            ->with('payments')
            ->orderBy('created_at', 'desc');

        $orders = $query->paginate(10);
        return OrderResource::collection($orders);
    }

    

}
