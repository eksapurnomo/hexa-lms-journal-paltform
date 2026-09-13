<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use App\Models\PaymentGateway;
use Razorpay\Api\Api;

class RazorPayment implements PaymentGatewayInterface
{

    public function processPayment($amount, array $data,  array $configInput)
    {

        $razorpay = new Api($configInput['key'], $configInput['secret']);

        try {
            $paymentLink = $razorpay->invoice->create([
                'type' => 'link',
                'amount' => $amount * 100, // amount in paisa
                'currency' => config('app.currency'),
                'description' => $data['product']['product'],
                'customer' => [
                    'name' => $data['customer']['name'],
                    'email' => $data['customer']['email'],
                    'contact' => $data['customer']['phone'],
                ],
                'callback_url' => route('razorpay.payment.success', $data['identifier']),
                // 'redirect' => true,
                // 'callback_method' => 'get',
                // 'cancel_url' => route('razorpay.payment.fail'),
            ]);

            $url = $paymentLink['short_url'];

            return redirect()->away($url);
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            // Handle signature verification error
            return response()->json(['error' => $e->getMessage()], 400);
        } catch (\Exception $e) {
            // Handle other exceptions
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
