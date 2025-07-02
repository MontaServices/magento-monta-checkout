<?php
/**
 * @author Jacco.Amersfoort <jacco.amersfoort@monta.nl>
 * @created 02/07/2025 16:15
 */
namespace Montapacking\MontaCheckout\Helper;

use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;

class Order
{
    /**
     * Order Comment field name
     */
    public const FIELD_NAME = 'montapacking_montacheckout_data';
}
