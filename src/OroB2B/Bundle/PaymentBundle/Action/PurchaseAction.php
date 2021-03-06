<?php

namespace OroB2B\Bundle\PaymentBundle\Action;

use OroB2B\Bundle\PaymentBundle\Entity\PaymentTransaction;
use OroB2B\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class PurchaseAction extends AbstractPaymentMethodAction
{
    const SAVE_FOR_LATER_USE = 'saveForLaterUse';

    /** {@inheritdoc} */
    protected function executeAction($context)
    {
        $options = $this->getOptions($context);

        $paymentTransaction = $this->paymentTransactionProvider->createPaymentTransaction(
            $options['paymentMethod'],
            PaymentMethodInterface::PURCHASE,
            $options['object']
        );

        $isPaymentMethodSupportsValidation = $this->isPaymentMethodSupportsValidation($paymentTransaction);
        if ($isPaymentMethodSupportsValidation) {
            $sourcePaymentTransaction = $this->paymentTransactionProvider
                ->getActiveValidatePaymentTransaction($options['paymentMethod']);

            if (!$sourcePaymentTransaction) {
                throw new \RuntimeException('Validation payment transaction not found');
            }

            $paymentTransaction->setSourcePaymentTransaction($sourcePaymentTransaction);
        }

        $paymentTransaction
            ->setAmount($options['amount'])
            ->setCurrency($options['currency']);

        if (!empty($options['transactionOptions'])) {
            $paymentTransaction->setTransactionOptions($options['transactionOptions']);
        }

        $response = $this->executePaymentTransaction($paymentTransaction);

        $this->paymentTransactionProvider->savePaymentTransaction($paymentTransaction);

        if ($isPaymentMethodSupportsValidation) {
            $sourcePaymentTransaction = $paymentTransaction->getSourcePaymentTransaction();
            $sourcePaymentTransactionOptions = $sourcePaymentTransaction->getTransactionOptions();
            if (empty($sourcePaymentTransactionOptions[self::SAVE_FOR_LATER_USE])) {
                $sourcePaymentTransaction->setActive(false);
                $this->paymentTransactionProvider->savePaymentTransaction($sourcePaymentTransaction);
            }
        }

        $this->setAttributeValue(
            $context,
            array_merge(
                [
                    'paymentMethod' => $options['paymentMethod'],
                    'paymentMethodSupportsValidation' => (bool)$isPaymentMethodSupportsValidation,
                ],
                $this->getCallbackUrls($paymentTransaction),
                (array)$paymentTransaction->getTransactionOptions(),
                $response
            )
        );
    }

    /**
     * @param PaymentTransaction $paymentTransaction
     * @return bool
     */
    protected function isPaymentMethodSupportsValidation(PaymentTransaction $paymentTransaction)
    {
        return $this->paymentMethodRegistry
            ->getPaymentMethod($paymentTransaction->getPaymentMethod())
            ->supports(PaymentMethodInterface::VALIDATE);
    }
}
