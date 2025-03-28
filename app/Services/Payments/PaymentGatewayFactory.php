<?php
namespace App\Services\Payments;

use App\Exceptions\ProcessPaymentException;
use App\Services\Payments\Gateways\PaypalPayment;
use App\Services\Payments\Gateways\CreditCardPayment;

class PaymentGatewayFactory {

    /**
     * @throws \Exception
     */
    public static function make($gateway): PaymentGatewayInterface {
        $paymentService = app(PaymentService::class);
        return match ($gateway) {
            'paypal' => new PaypalPayment($paymentService),
            'credit_card' => new CreditCardPayment($paymentService),
            default => throw new ProcessPaymentException('Unsupported payment gateway'),
        };
    }
}
