<?php
declare(strict_types=1);

namespace DepositPayments\AdminDeposit\Model\Payment;

use DepositPayments\AdminDeposit\Block\Form\Deposit as DepositForm;
use DepositPayments\AdminDeposit\Block\Info\Deposit as DepositInfo;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Payment\Model\Method\AbstractMethod;

class Deposit extends AbstractMethod
{
    public const CODE = 'admin_deposit';
    public const AMOUNT_KEY = 'deposit_amount';

    /** @var string */
    protected $_code = self::CODE;

    /** @var bool */
    protected $_isOffline = true;

    /** @var bool */
    protected $_canUseInternal = true;

    /** @var bool */
    protected $_canUseCheckout = false;

    /** @var bool */
    protected $_canAuthorize = false;

    /** @var bool */
    protected $_canCapture = false;

    /** @var bool */
    protected $_canCapturePartial = false;

    /** @var bool */
    protected $_canRefund = false;

    /** @var bool */
    protected $_canVoid = false;

    /** @var string */
    protected $_formBlockType = DepositForm::class;

    /** @var string */
    protected $_infoBlockType = DepositInfo::class;

    public function assignData(DataObject $data): self
    {
        parent::assignData($data);

        $additionalData = $data->getData('additional_data');
        $amount = is_array($additionalData)
            ? ($additionalData[self::AMOUNT_KEY] ?? null)
            : null;

        if ($amount === null) {
            $amount = $data->getData(self::AMOUNT_KEY);
        }

        $this->getInfoInstance()->setAdditionalInformation(self::AMOUNT_KEY, $amount);

        return $this;
    }

    /**
     * @throws LocalizedException
     */
    public function validate(): self
    {
        parent::validate();

        $paymentInfo = $this->getInfoInstance();
        $rawAmount = $paymentInfo->getAdditionalInformation(self::AMOUNT_KEY);

        if (!is_numeric($rawAmount)) {
            throw new LocalizedException(__('Enter a valid deposit amount.'));
        }

        $amount = (float) $rawAmount;
        $document = $paymentInfo->getOrder() ?: $paymentInfo->getQuote();
        $grandTotal = $document ? (float) $document->getGrandTotal() : 0.0;

        if ($amount <= 0.0) {
            throw new LocalizedException(__('The deposit amount must be greater than zero.'));
        }

        if ($grandTotal <= 0.0 || $amount > $grandTotal) {
            throw new LocalizedException(
                __('The deposit amount cannot be greater than the order total.')
            );
        }

        $paymentInfo->setAdditionalInformation(self::AMOUNT_KEY, $amount);

        return $this;
    }
}
