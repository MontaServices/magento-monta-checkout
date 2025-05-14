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
            'coreSoftware' => 'Magento',
            'coreVersion' => InstalledVersions::getVersion('magento/product-community-edition'),
            'checkoutApiWrapperVersion' => InstalledVersions::getVersion('monta/checkout-api-wrapper'),
            'moduleName' => $moduleName,
            'moduleVersion' => $this->resource->getDbVersion($moduleName),
            'phpVersion' => PHP_VERSION,
            'operatingSystem' => PHP_OS,
        ];
    }
}
