<?php

namespace Montapacking\MontaCheckout\Model\Config\Provider;

use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfig;
use Magento\Framework\Module\Manager;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractConfigProvider
{
    /**
     * AbstractConfigProvider constructor.
     *
     * @param ScopeConfig $scopeConfig
     * @param Manager $moduleManager
     */
    public function __construct(
        protected readonly ScopeConfig $scopeConfig,
        protected readonly Manager $moduleManager
    )
    {
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isModuleOutputEnabled()
    {
        return $this->moduleManager->isOutputEnabled('Montapacking_MontaCheckout');
    }
}
