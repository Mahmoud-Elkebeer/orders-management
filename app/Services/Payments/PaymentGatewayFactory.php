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
        return match ($gateway) {
            'paypal' => new PaypalPayment(),
            'credit_card' => new CreditCardPayment(),
            default => throw new ProcessPaymentException('Unsupported payment gateway'),
        };
    }
}
