<?php

namespace Montapacking\MontaCheckout;

use Magento\Framework\Module\Manager as ModuleManager;

class DeactivationHelper
{
    protected $moduleManager;

    public function __construct(
        ModuleManager $moduleManager
    ) {
        $this->moduleManager = $moduleManager;
    }

    public function execute()
    {
        if (!$this->moduleManager->isEnabled('Montapacking_MontaCheckout')) {
//            return; // Stop de uitvoering als de module is uitgeschakeld
        }

        // Rest van je code
    }
}
