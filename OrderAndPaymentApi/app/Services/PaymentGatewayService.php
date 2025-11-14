<?php

namespace App\Services;

use App\Models\Payment;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class PaymentGatewayService
{
    protected string $url;

    public function __construct()
    {
        $this->url = config('services.payment_gateway.url') ?? env('PAYMENT_GATEWAY_URL');
    }


    //Envio el pago y retorna el estado del pago
    public function confirmPayment(Payment $payment): array
    {
        try {
            $response = Http::timeout(5)->post($this->url, [
                'payment_id' => $payment->id,
                'order_id' => $payment->order_id,
                'amount' => $payment->amount,
                'attempt' => $payment->attempt_number,
            ]);
        } catch (\Throwable $e ) {
            return ['status' => 'failed', 'reference' => null];
        }

        $body = $response->json();

        return[
            'status' => Arr::get($body, 'status', 'failed'),
            'reference' => Arr::get($body, 'reference', 'null'),
        ];
    }
}