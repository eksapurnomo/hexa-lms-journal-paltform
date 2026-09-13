<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Stripe\Stripe;

class StripePayment implements PaymentGatewayInterface
{
    public function processPayment($amount, array $data, array $config)
    {
        Stripe::setApiKey($config['secret_key']);
        $session = \Stripe\Checkout\Session::create([
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => config('app.currency'),
                        'product_data' => ['name' => $data['product']['product']],
                        'unit_amount' => $amount * 100,
                    ],
                    'quantity' => 1,
                ]
            ],
            'mode' => 'payment',
            'success_url' => route('stripe.payment.success', $data['identifier']),
            'cancel_url' => route('stripe.payment.cancel'),
        ], ['api_key' => $config['secret_key']]);
        return redirect($session->url);
    }
}
