<?php

namespace App\Http\Controllers\WebAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentGatewayUpdateRequest;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        $paypal = PaymentGateway::query()->where('name', 'paypal')->first();
        $stripe = PaymentGateway::query()->where('name', 'stripe')->first();
        $twoCheckout = PaymentGateway::query()->where('name', '2checkout')->first();
        $aamarpay = PaymentGateway::query()->where('name', 'aamarpay')->first();
        $razorpay = PaymentGateway::query()->where('name', 'razorpay')->first();


        return view('payment_gateway.index', [
            'paypal' => $paypal,
            'stripe' => $stripe,
            'twoCheckout' => $twoCheckout,
            'aamarpay' => $aamarpay,
            'razorpay' => $razorpay,
            'paypalConfig' => json_decode($paypal?->config ?? '{}'),
            'stripeConfig' => json_decode($stripe?->config ?? '{}'),
            'twoCheckoutConfig' => json_decode($twoCheckout?->config ?? '{}'),
            'aamarpayConfig' => json_decode($aamarpay?->config ?? '{}'),
            'razorpayConfig' => json_decode($razorpay?->config ?? '{}'),

        ]);
    }

    public function update(PaymentGateway $paymentGateway, PaymentGatewayUpdateRequest $request)
    {
        if (app()->isLocal()) {
            return to_route('payment_gateway.index')->withError('Payment gateway cannot be updated in demo mode');
        }

        $isActive = false;
        if (isset($request->is_active)) {
            $isActive = $request->is_active == 'on' ? true : false;
        }

        $paymentGateway->update([
            'config' => json_encode($request->except(['_token', '_method'])),
            'is_active' => $isActive
        ]);

        return to_route('payment_gateway.index')->withSuccess('Payment gateway updated');
    }
}
