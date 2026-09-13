<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Support\Facades\Http;

class PaymobPayment implements PaymentGatewayInterface
{
    public function processPayment($amount, array $data, array $config)
    {
        $response = Http::withHeaders([
            'Authorization' => 'Token ' . $config['secret_key'],
        ])
        ->post('https://accept.paymob.com/v1/intention/', [
            'amount' => $amount,
            'currency' => config('app.currency'),
            'payment_methods' => [
                $config['integration_id'],
            ],
            'items' => [
                [
                    'name' => $data['product']['product'],
                    'amount' => $amount,
                    'unit_price' => $amount,
                    'quantity' => 1,
                ],
            ],
            'billing_data' => [
                'first_name' => $data['customer']['name'],
                'email' => $data['customer']['email'],
                'phone' => $data['customer']['phone'],
            ],
            'redirection_url' => route('paymob.payment.success', $data['identifier']),
        ]);

        if ($response->ok()) {
            $responseData = json_decode($response->getBody()->getContents(), true);
            return redirect("https://accept.paymob.com/unifiedcheckout/?publicKey={$config['public_key']}&clientSecret={$responseData['client_secret']}}");
        }

        return to_route('paymob.payment.fail');
    }
}
