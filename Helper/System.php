<?php
/**
 * @author Jacco.Amersfoort <jacco.amersfoort@monta.nl>
 * @created 5/14/2025 10:59
 */
namespace Montapacking\MontaCheckout\Helper;

use Composer\InstalledVersions;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Module\ResourceInterface;
use Monta\CheckoutApiWrapper\Objects\Settings;

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
    public function getInfo(): array
    {
        $moduleName = $this->_getModuleName();
        return [
            Settings::CORE_SOFTWARE => 'Magento',
            Settings::CORE_VERSION => $this->getComposerVersion('magento/product-community-edition'),
            Settings::CHECKOUT_API_WRAPPER_VERSION => $this->getComposerVersion('monta/checkout-api-wrapper'),
            Settings::MODULE_NAME => $moduleName,
            Settings::MODULE_VERSION => $this->resource->getDbVersion($moduleName),
            Settings::PHP_VERSION => PHP_VERSION,
            Settings::OPERATING_SYSTEM => PHP_OS,
        ];
    }

    /**
     * @param string $packageName
     * @return string
     */
    protected function getComposerVersion(string $packageName): string
    {
        try {
            return InstalledVersions::getVersion($packageName);
        } catch (\OutOfBoundsException $e) {
            // When module not installed, catch error and return empty string
            return "";
        }
    }
}
