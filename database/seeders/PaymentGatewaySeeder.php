<?php

namespace Database\Seeders;

use App\Enum\MediaTypeEnum;
use App\Models\Media;
use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PaymentGateway::truncate();

        $gateways = [
            'paypal' => [
                'mode' => 'sandbox',
                'app_id' => config('services.paypal.app_id', 'PAYPAL_APP_ID_PLACEHOLDER'),
                'client_id' => config('services.paypal.client_id', 'PAYPAL_CLIENT_ID_PLACEHOLDER'),
                'client_secret' => config('services.paypal.client_secret', 'PAYPAL_CLIENT_SECRET_PLACEHOLDER'),
            ],
            'stripe' => [
                'publishable_key' => config('services.stripe.key', 'STRIPE_KEY_PLACEHOLDER'),
                'secret_key' => config('services.stripe.secret', 'STRIPE_SECRET_PLACEHOLDER'),
            ],
            '2checkout' => [
                'merchant' => config('services.2checkout.merchant', '2CHECKOUT_MERCHANT_PLACEHOLDER'),
                'currency' => 'USD',
            ],
            'aamarpay' => [
                'store_id' => config('services.aamarpay.store_id', 'aamarpaytest'),
                'signature_key' => config('services.aamarpay.signature_key', 'AAMARPAY_SIGNATURE_KEY_PLACEHOLDER'),
                'currency' => 'BDT',
            ],
            'razorpay' => [
                'title' => 'Razorpay',
                'name' => 'razorpay',
                'key' => config('services.razorpay.key', 'RAZORPAY_KEY_PLACEHOLDER'),
                'secret' => config('services.razorpay.secret', 'RAZORPAY_SECRET_PLACEHOLDER'),
                'mode' => 'test',
                'alias' => 'Razorpay',
                'is_active' => true,
            ],
            // 'paymob' => [
            //     'public_key' => '',
            //     'secret_key' => '',
            //     'integration_id' => '',
            // ],
        ];

        foreach ($gateways as $name => $config) {
            $paymentGateway = PaymentGateway::updateOrCreate([
                'name' => $name,
            ],[
                'config' => json_encode($config),
                'type' => 'test',
                'is_active' => true,
            ]);

            switch ($name) {
                case 'paypal':
                    $media = Media::factory()->create([
                        'type' => MediaTypeEnum::IMAGE,
                        'src' => 'assets/images/payment/Paypal.png',
                        'path' => 'assets/images/payment/',
                        'extension' => 'png',
                    ]);
                    $paymentGateway->update([
                        'media_id' => $media->id
                    ]);
                    break;
                case 'stripe':
                    $media = Media::factory()->create([
                        'type' => MediaTypeEnum::IMAGE,
                        'src' => 'assets/images/payment/Stripe.png',
                        'path' => 'assets/images/payment/',
                        'extension' => 'png',
                    ]);
                    $paymentGateway->update([
                        'media_id' => $media->id
                    ]);
                    break;
                case '2checkout':
                    $media = Media::factory()->create([
                        'type' => MediaTypeEnum::IMAGE,
                        'src' => 'assets/images/payment/2checkout.png',
                        'path' => 'assets/images/payment/',
                        'extension' => 'png',
                    ]);
                    $paymentGateway->update([
                        'media_id' => $media->id
                    ]);
                    break;
                case 'aamarpay':
                    $media = Media::factory()->create([
                        'type' => MediaTypeEnum::IMAGE,
                        'src' => 'assets/images/payment/Aamarpay.png',
                        'path' => 'assets/images/payment/',
                        'extension' => 'png',
                    ]);
                    $paymentGateway->update([
                        'media_id' => $media->id
                    ]);
                    break;
                case 'razorpay':
                    $media = Media::factory()->create([
                        'type' => MediaTypeEnum::IMAGE,
                        'src' => 'assets/images/payment/Razorpay.png',
                        'path' => 'assets/images/payment/',
                        'extension' => 'png',
                    ]);
                    $paymentGateway->update([
                        'media_id' => $media->id
                    ]);
                    break;
                // case 'paymob':
                //     $media = Media::factory()->create([
                //         'type' => MediaTypeEnum::IMAGE,
                //         'src' => 'assets/images/payment/Paymob.png',
                //         'path' => 'assets/images/payment/',
                //         'extension' => 'png',
                //     ]);
                //     $paymentGateway->update([
                //         'media_id' => $media->id
                //     ]);
                //     break;
            }
        }
    }
}
