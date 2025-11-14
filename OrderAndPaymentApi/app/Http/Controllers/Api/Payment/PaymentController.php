<?php

namespace App\Http\Controllers\Api\Payment;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePaymentRequest;
use App\Http\Requests\UpdatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Services\PaymentGatewayService;

class PaymentController extends Controller
{
    protected PaymentGatewayService $gateway;

    public function __construct(PaymentGatewayService $gateway)
    {
        $this->gateway = $gateway;
    }

    //Listar los pagos
    public function index(Request $request)
    {
        $query = Payment::with('order')->orderBy('created_At', 'desc');

        if ($request->filled('id')) {
            $query->where('id', $request->input('id'));
        }

        if ($request->filled('status')) {
            $query->where('id', $request->input('status'));
        }

        $payments = $query->paginate(10);

        return PaymentResource::collection($payments);
    }

    //Mostrar un pago especifico
    public function show($id): JsonResponse
    {
        $payment = Payment::withTrashed()->find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Pago encontrado'
            ], 404);
        }

        if ($payment->trashed()) {
            return response()->json([
                'message' => 'Este pago esta eliminado'
            ], 422);
        }

        return (new PaymentResource($payment))
            ->additional(['message' => 'Pago encontrado'])
            ->response()
            ->setStatusCode(200);
    }

    //Pagar las ordenes
    public function store(StorePaymentRequest $request, Order $order): JsonResponse
    {
        if ($order->trashed()) {
            return response()->json(['message' => 'No se puede pagar una orden eliminada'], 422);
        }

        if ($order->status === 'paid') {
            return response()->json(['message' => 'Pedido ya pagado'], 409);
        }

        $data = $request->validated();

        $attemptNumber = $order->payments()->count() + 1;

        $amountInCents = $data['amount'] * 100;

        $payment = DB::transaction(function () use ($order, $data, $attemptNumber, $amountInCents) {
            return Payment::create([
                'order_id' => $order->id,
                'amount' => $amountInCents,
                'status' => 'pending',
                'payment_method' => $data['payment_method'] ?? 'tarjeta',
                'attempt_number' => $attemptNumber,
            ]);
        });

        if ($payment->amount !== $order->total_amount) {
            $payment->update(['status' => 'failed']);
            $order->update(['status' => 'failed']);

            return (new PaymentResource($payment))
                ->additional(['message' => 'El monto del pago debe ser igual al total del pedido'])
                ->response()
                ->setStatusCode(422);
        }

        $result = $this->gateway->confirmPayment($payment);

        DB::transaction(function () use ($order, $payment, $result) {
            if ($result['status'] === 'success') {
                $payment->update([
                    'status' => 'success',
                    'external_reference' => $result['reference'] ?? null,
                ]);
                $order->update(['status' => 'paid']);
            } else {
                $payment->update([
                    'status' => 'failed',
                    'external_reference' => $result['reference'] ?? null,
                ]);
                $order->update(['status' => 'failed']);
            }
        });

        return (new PaymentResource($payment->fresh()))
            ->additional(['message' => 'Pago creado correctamente'])
            ->response()
            ->setStatusCode(201);
    }


    //Actualizar un pago
    public function update(UpdatePaymentRequest $request, $id): JsonResponse
    {
        $payment = Payment::withTrashed()->find($id);

        if (!$payment) {
            return response()->json([
                'message' => 'Pago no encontrado'
            ], 404);
        }

        if ($payment->trashed()) {
            return response()->json([
                'message' => 'Este pago esta eliminado y no se puede actualizar'
            ], 422);
        }

        $data = $request->validated();

        $payment->fill($data);

        if (!$payment->isDirty()) {
            return (new PaymentResource($payment))
                ->additional(['message' => 'No se realizaron cambios'])
                ->response()
                ->setStatusCode(200);
        }

        $updated = DB::transaction(function () use ($payment) {
            $payment->save();
            return $payment->fresh();
        });


        return (new PaymentResource($updated))
            ->additional(['message' => 'Pago actualizado correctamente'])
            ->response()
            ->setStatusCode(200);
    }


    //Remover logicamente un pago
    public function destroy($id): JsonResponse
    {
        $payment = Payment::find($id);

        if (!$payment) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        DB::transaction(function () use ($payment) {
            $payment->delete();
        });

        return (new PaymentResource($payment))
            ->additional(['message' => 'Pago eliminado correctamente'])
            ->response()
            ->setStatusCode(200);
    }


    /*Restaurar Pago Eliminado */
    public function restore($id): JsonResponse
    {
        $payment = Payment::withTrashed()->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        if (!$payment->trashed()) {
            return response()->json(['message' => 'Este Pago no esta eliminado'], 422);
        }

        DB::transaction(function () use ($payment) {
            $payment->restore();
        });

        return (new PaymentResource($payment->fresh()))
            ->additional(['message' => 'Pago restaurado correctamente'])
            ->response()
            ->setStatusCode(200);
    }

    /*Listar Pagos eliminadas */
    public function trashed(Request $request)
    {
        $payment = Payment::onlyTrashed()->with('order')->orderBy('deleted_at', 'desc');

        if ($request->filled('id')) {
            $payment->where('id', $request->input('id'));
        }

        $payments = $payment->paginate(10);

        if ($payments->isEmpty()) {
            return response()->json(['message' => 'No se encontraron ordenes eliminadas'], 200);
        }

        return PaymentResource::collection($payments);
    }

    /*Mostrar Pago elimiado especifica */
    public function showWithTrashed($id)
    {
        $payment = Payment::withTrashed()->with('order')->find($id);

        if (!$payment) {
            return response()->json(['message' => 'Pago no encontrado'], 404);
        }

        if (!$payment->trashed()) {
            return response()->json(['message' => 'Este pago no esta eliminado'], 404);
        }

        return (new PaymentResource($payment))
            ->additional(['message' => 'Pago eliminado'])
            ->response()
            ->setStatusCode(200);
    }

    //Listar pagos completos
    public function success(Request $request)
    {
        $query = Payment::where('status', 'success')->with('order')->orderBy('created_at', 'desc');
        $payments = $query->paginate(10);
        return PaymentResource::collection($payments);
    }

    //Listar pagos fallidos
    public function failed(Request $request)
    {
        $query = Payment::where('status', 'failed')->with('order')->orderBy('created_at', 'desc');
        $payments = $query->paginate(10);
        return PaymentResource::collection($payments);
    }
}
