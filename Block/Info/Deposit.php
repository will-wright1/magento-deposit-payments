<?php
declare(strict_types=1);

namespace DepositPayments\AdminDeposit\Block\Info;

use DepositPayments\AdminDeposit\Model\Payment\Deposit as DepositMethod;
use Magento\Framework\DataObject;
use Magento\Payment\Block\Info as PaymentInfo;

class Deposit extends PaymentInfo
{
    protected function _prepareSpecificInformation($transport = null): DataObject
    {
        $transport = parent::_prepareSpecificInformation($transport);
        $amount = $this->getInfo()->getAdditionalInformation(DepositMethod::AMOUNT_KEY);

        if ($amount !== null && $amount !== '') {
            $order = $this->getInfo()->getOrder();
            $formattedAmount = $order
                ? $order->formatPriceTxt((float) $amount)
                : (string) $amount;

            $transport->setData((string) __('Deposit Paid'), $formattedAmount);
        }

        return $transport;
    }
}
