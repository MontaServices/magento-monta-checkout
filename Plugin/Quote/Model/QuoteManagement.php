<?php

namespace Montapacking\MontaCheckout\Plugin\Quote\Model;

use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class QuoteManagement
{
    /** @var CartRepositoryInterface $cartRepository */
    private $cartRepository;

    /** @var OrderRepositoryInterface $orderRepository */
    private $orderRepository;

    /**
     * QuoteManagement constructor.
     *
     * @param CartRepositoryInterface $cartRepository
     */
    public function __construct(
        CartRepositoryInterface $cartRepository,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param $subject
     * @param $cartId
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    // @codingStandardsIgnoreLine
    public function beforePlaceOrder($subject, $cartId)
    {
        $quote = $this->cartRepository->getActive($cartId);
        $shippingAddress = $quote->getShippingAddress();
        $billingAddress = $quote->getBillingAddress();
        $deliveryOption = $shippingAddress->getMontapackingMontacheckoutData();

        if (!$deliveryOption) {
            return;
        }

        $deliveryOption = json_decode($deliveryOption);

        $type = $deliveryOption->type;

        if ($type == 'pickup') {

            $newAddress = $deliveryOption->additional_info[0];

            $shippingAddress->setStreet($newAddress->street . ' ' . $newAddress->housenumber);
            $shippingAddress->setCompany($newAddress->company);
            $shippingAddress->setPostcode($newAddress->postal);
            $shippingAddress->setCity($newAddress->city);
            $shippingAddress->setCountryId($newAddress->country);
        }
    }

    /**
     * @param $subject
     * @param $orderId
     * @param $quoteId
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    // @codingStandardsIgnoreLine
    public function afterPlaceOrder($subject, $orderId, $quoteId)
    {
        $order = $this->orderRepository->get($orderId);

        if ($order->getMontapackingMontacheckoutData()) {
            return $orderId;
        }

        $quote = $this->cartRepository->get($quoteId);
        $address = $quote->getShippingAddress();
        $deliveryOption = $address->getMontapackingMontacheckoutData();

        if (!$deliveryOption) {
            return $orderId;
        }

        $order->setMontapackingMontacheckoutData($deliveryOption);
        $order->save();

        return $orderId;
    }
}
