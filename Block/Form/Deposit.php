<?php
declare(strict_types=1);

namespace DepositPayments\AdminDeposit\Block\Form;

use Magento\Payment\Block\Form as PaymentForm;

class Deposit extends PaymentForm
{
    /** @var string */
    protected $_template = 'DepositPayments_AdminDeposit::form/deposit.phtml';

    public function getDepositAmount(): string
    {
        $amount = $this->getMethod()
            ->getInfoInstance()
            ->getAdditionalInformation('deposit_amount');

        return is_scalar($amount) ? (string) $amount : '';
    }
}
