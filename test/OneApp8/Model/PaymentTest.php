<?php
namespace OneApp8\Model;

use PHPUnit\Framework\TestCase;
use OneApp8\Model\Payment;
use OneApp8\Model\Order;

/**
 * Class PaymentTest
 * @author globrutto
 */
class PaymentTest extends TestCase
{
    public function testCreatePayment()
    {
        $order = new Order();
        $order->setId(1);
        $order->setWalletId(4);

        $payment = new Payment();
        $this->assertNotNull($payment);
        $payment->setOrder($order);
        $this->assertNotNull($payment->getOrder());
    }
}
