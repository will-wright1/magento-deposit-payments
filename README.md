# Magento 2 Admin Deposit Payment

A Composer-installable Magento 2 module for recording an initial deposit while an administrator creates an order.

## Behaviour

- Adds a **Deposit payment** method to Admin order creation only.
- Requires the administrator to enter an amount greater than zero and no greater than the order grand total.
- Preserves Magento's original `grand_total` and `base_grand_total`.
- Stores the paid deposit and remaining balance on the order in both order and base currency.
- Places the order in Magento's `processing` state and default processing status.
- Shows the full total, deposit paid, and balance due on the Admin order view and order grid.

This is an offline recording method. It records money received outside Magento; it does not contact a payment gateway, create an invoice, or alter Magento's native `total_paid` value.

## Requirements

- Magento Open Source or Adobe Commerce 2.4.x
- PHP 8.1+

## Installation

Add the package to a Composer repository (or use a local path repository), then run:

```bash
composer require deposit-payments/module-admin-deposit
bin/magento module:enable DepositPayments_AdminDeposit
bin/magento setup:upgrade
bin/magento cache:flush
```

For production mode, also run the normal dependency injection compilation and static content deployment steps for the store.

## Configuration

Go to **Stores > Configuration > Sales > Payment Methods > Admin Deposit Payment**. The method is enabled by default and is never exposed in storefront checkout.

To use it, create an order in **Sales > Orders > Create New Order**, select **Deposit payment**, and enter the amount received in the displayed order currency.

## Stored order fields

| Field | Meaning |
| --- | --- |
| `deposit_paid` | Deposit in the order currency |
| `base_deposit_paid` | Deposit in the base currency |
| `deposit_balance_due` | Remaining balance in the order currency |
| `base_deposit_balance_due` | Remaining balance in the base currency |

The order grid also stores `deposit_paid` and `deposit_balance_due` for filtering and display.
