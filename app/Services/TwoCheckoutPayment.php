<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;

class TwoCheckoutPayment implements PaymentGatewayInterface
{
    public function processPayment($amount, array $data, array $config)
    {
        $config['merchant'] = '254928155937';
        $currency = config('app.currency');

        $url = "https://secure.2checkout.com/checkout/buy?merchant=" . $config['merchant'] . "&currency=$currency&tpl=one-column&dynamic=1&prod=" . $data['product']['product'] . "&price=" . $amount . "&type=digital&qty=1&signature=2e484be365bd77cd79d8759a3507feaf6ea3af6fee44a3c682dbddd17ee929bb";

        return redirect($url);
    }
}
