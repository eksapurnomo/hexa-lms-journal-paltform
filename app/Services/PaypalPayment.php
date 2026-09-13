<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Illuminate\Support\Facades\Session;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;

class PaypalPayment implements PaymentGatewayInterface
{
    public function processPayment($amount, array $data,  array $configInput)
    {
        $environment =  $configInput['mode'] === 'live'
            ? new ProductionEnvironment($configInput['client_id'], $configInput['client_secret'])
            : new SandboxEnvironment($configInput['client_id'], $configInput['client_secret']);

        $client = new PayPalHttpClient($environment);

        $request = new OrdersCreateRequest();
        $request->prefer('return=representation');
        $request->body = [
            'intent' => 'CAPTURE',
            'purchase_units' => [[
                'amount' => [
                    'currency_code' => 'USD',
                    'value' =>  $amount,
                ],
            ]],
            'application_context' => [
                'return_url' => route('paypal.payment.success', $data['identifier'], $environment),
                'cancel_url' => route('paypal.payment.cancel'),
            ],
        ];

        try {
            $response = $client->execute($request);
            $url = $response->result->links[1]->href; // Redirect to PayPal for payment approval
            return  redirect()->away($url);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
