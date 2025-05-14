<?php
/**
 * @author Jacco.Amersfoort <jacco.amersfoort@monta.nl>
 * @created 5/14/2025 10:59
 */
namespace Montapacking\MontaCheckout\Helper;

use Composer\InstalledVersions;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Module\ResourceInterface;

class System extends AbstractHelper
{
    /**
     * @param ResourceInterface $resource
     */
    public function __construct(
        protected readonly ResourceInterface $resource)
    {
    }

    /** Collect system information
     *
     * @return string[]
     */
    public function getDiagnostics()
    {
        $moduleName = $this->_getModuleName();
        return [
            'core_software' => 'Magento',
            'core_version' => InstalledVersions::getVersion('magento/product-community-edition'),
            'checkout_api_wrapper_version' => InstalledVersions::getVersion('monta/checkout-api-wrapper'),
            'module_name' => $moduleName,
            'module_version' => $this->resource->getDbVersion($moduleName),
            'php_version' => PHP_VERSION,
            'operating_system' => PHP_OS,
        ];
    }
}
