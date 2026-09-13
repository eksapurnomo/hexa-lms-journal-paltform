<?php

namespace App\Services\Initialization;

use App\Models\PaymentGateway;

class PaymentGatewayInitializer implements InitializerInterface
{
    private const GATEWAYS = [
        'paypal',
        'stripe',
        '2checkout',
        'aamarpay',
        'razorpay'
    ];

    public function getName(): string
    {
        return 'Payment Gateways';
    }

    public function initialize(bool $dryRun = false): InitializationResult
    {
        $result = new InitializationResult();

        foreach (self::GATEWAYS as $gatewayName) {
            $exists = PaymentGateway::query()->where('name', $gatewayName)->exists();

            if ($exists) {
                $result->record('skip', sprintf('Gateway %s already exists', $gatewayName));
                continue;
            }

            if (!$dryRun) {
                PaymentGateway::query()->create([
                    'name' => $gatewayName,
                    'is_active' => false,
                    'config' => json_encode(new \stdClass()),
                    'type' => 'test' // original seeder sets type to test
                ]);
            }

            $result->record('create', sprintf('Payment gateway: %s', $gatewayName));
        }

        return $result;
    }
}
