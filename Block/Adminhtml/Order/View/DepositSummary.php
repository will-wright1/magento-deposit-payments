<?php
declare(strict_types=1);

namespace DepositPayments\AdminDeposit\Block\Adminhtml\Order\View;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;

class DepositSummary extends Template
{
    public function __construct(
        Context $context,
        private readonly Registry $registry,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    public function getOrder(): ?Order
    {
        $order = $this->registry->registry('current_order');

        return $order instanceof Order ? $order : null;
    }

    public function isDepositOrder(): bool
    {
        $order = $this->getOrder();

        return $order !== null && $order->getData('deposit_paid') !== null;
    }

    public function formatPrice(float $amount): string
    {
        $order = $this->getOrder();

        return $order ? $order->formatPrice($amount) : (string) $amount;
    }
}
