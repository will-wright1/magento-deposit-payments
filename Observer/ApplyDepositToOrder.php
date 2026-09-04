<?php
declare(strict_types=1);

namespace DepositPayments\AdminDeposit\Observer;

use DepositPayments\AdminDeposit\Model\Payment\Deposit;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config as OrderConfig;

class ApplyDepositToOrder implements ObserverInterface
{
    public function __construct(
        private readonly PriceCurrencyInterface $priceCurrency,
        private readonly OrderConfig $orderConfig
    ) {
    }

    public function execute(Observer $observer): void
    {
        /** @var Order|null $order */
        $order = $observer->getEvent()->getOrder();
        if (!$order || !$order->getPayment() || $order->getPayment()->getMethod() !== Deposit::CODE) {
            return;
        }

        $deposit = (float) $order->getPayment()->getAdditionalInformation(Deposit::AMOUNT_KEY);
        $grandTotal = (float) $order->getGrandTotal();
        $baseGrandTotal = (float) $order->getBaseGrandTotal();

        // The payment method validates this before placement; guard again to avoid corrupt totals.
        if ($deposit <= 0.0 || $deposit > $grandTotal) {
            throw new LocalizedException(__('The deposit amount is invalid for this order.'));
        }

        $balance = $this->priceCurrency->round(max(0.0, $grandTotal - $deposit));
        $baseDeposit = $grandTotal > 0.0
            ? $this->priceCurrency->round($baseGrandTotal * ($deposit / $grandTotal))
            : 0.0;
        $baseBalance = $this->priceCurrency->round(max(0.0, $baseGrandTotal - $baseDeposit));

        $order->setData('deposit_paid', $deposit);
        $order->setData('base_deposit_paid', $baseDeposit);
        $order->setData('deposit_balance_due', $balance);
        $order->setData('base_deposit_balance_due', $baseBalance);
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus($this->orderConfig->getStateDefaultStatus(Order::STATE_PROCESSING));
        $order->addCommentToStatusHistory(
            __('A deposit of %1 was recorded. Remaining balance: %2.',
                $order->formatPriceTxt($deposit),
                $order->formatPriceTxt($balance)
            ),
            $order->getStatus(),
            false
        );
    }
}
