<?php
namespace App\Interfaces;

interface PaymentGatewayInterface
{
    public function processPayment($amount, array $data, array $config);
}
