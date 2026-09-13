<?php

namespace App\Services;

use App\Interfaces\PaymentGatewayInterface;
use Exception;

class AamarPayment implements PaymentGatewayInterface
{
    /**
     * Process the payment using Aamar Payment Gateway.
     *
     * @param float $amount The amount to be paid.
     * @param array $data Additional data for the payment.
     * @param array $config Configuration for the payment.
     */
    public function processPayment($amount, array $data, array $config)
    {

        // Generate a unique transaction ID
        $tran_id = $data['identifier'];

        // Set the currency to use (BDT for Bangladeshi Taka)
        $currency = 'BDT';

        // Store ID provided by Aamar Payment
        $store_id = $config['store_id'];

        // Signature key provided by Aamar Payment
        $signature_key = $config['signature_key'];

        // URL to send the payment request
        $url = "https://sandbox.aamarpay.com/jsonpost.php";

        // Initialize cURL
        $curl = curl_init();
        // Set cURL options
        curl_setopt_array(
            $curl,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_POSTFIELDS => '{' .
                    '"store_id": "' . $store_id . '",' .
                    '"tran_id": "' . $tran_id . '",' .
                    '"success_url": "' . route('aamrpay.payment.success') . '",' .
                    '"fail_url": "' . route('aamrpay.payment.fail') . '",' .
                    '"cancel_url": "' . route('aamrpay.payment.cancel') . '",' .
                    '"amount": "' . $amount . '",' .
                    '"currency": "' . $currency . '",' .
                    '"signature_key": "' . $signature_key . '",' .
                    '"desc": "Merchant Registration Payment",' .
                    '"cus_name": "' . $data['customer']['name'] . '",' .
                    '"cus_email": "' . $data['customer']['email'] . '",' .
                    '"cus_add1": "N/A",' .
                    '"cus_add2": "N/A",' .
                    '"cus_city": "N/A",' .
                    '"cus_state": "N/A",' .
                    '"cus_postcode": "0000",' .
                    '"cus_country": "Bangladesh",' .
                    '"cus_phone": "' . $data['customer']['phone'] . '",' .
                    '"type": "json"' .
                    '}',
                CURLOPT_HTTPHEADER => array(
                    'Content-Type: application/json'
                ),
            )
        );

        // Execute the cURL request and get the response
        $response = curl_exec($curl);

        // Close the cURL session
        curl_close($curl);

        // Decode the JSON response
        $responseObj = json_decode($response);

        // If a payment URL is returned, redirect the user to it
        if (isset($responseObj->payment_url) && !empty($responseObj->payment_url)) {

            $paymentUrl = $responseObj->payment_url;
            return redirect($paymentUrl);
        } else {
            // Otherwise, echo the response
            echo $response;
        }
    }
}
