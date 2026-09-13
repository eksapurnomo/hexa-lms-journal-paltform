<?php

namespace App\Services;

use App\Models\PaymentGateway;

class PaymentService
{
    public function processPayment($amount, array $data,)
    {
        $gateway = PaymentGateway::where('name', $data['gateway'])->firstOrFail();
        if ($gateway->is_active == 0) {
            return redirect()->back()->with('error', 'Payment gateway is not active');
        }
        $config = json_decode($gateway->config, true);
        switch ($gateway->name) {
            case 'paypal':
                return (new PaypalPayment())->processPayment($amount, $data, $config);
            case '2checkout':
                return (new TwoCheckoutPayment())->processPayment($amount, $data, $config);
            case 'stripe':
                return (new StripePayment())->processPayment($amount, $data, $config);
            case 'aamarpay':
                return (new AamarPayment())->processPayment($amount, $data, $config);
            case 'razorpay':
                return (new RazorPayment())->processPayment($amount, $data, $config);
            default:
                throw new \Exception("Unsupported payment gateway");
        }
    }


    public function paymentView()
    {
        $gateway = PaymentGateway::where('is_active', 1)->firstOrFail();
        switch ($gateway->name) {
            case 'paypal':
                return view('payment.paypal.paypal');
            case '2checkout':
                return view('index');
            case 'stripe':
                return view('payment.stripe.stripe');
            case 'aamarpay':
                return view('payment.sslcommerz');
            default:
                throw new \Exception("Unsupported payment gateway");
        }
    }
}
