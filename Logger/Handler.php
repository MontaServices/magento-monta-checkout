<?php

namespace Montapacking\Montacheckout\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

class Handler extends Base
{
    /** @var int $loggerType - Logging level */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/montapacking_checkout.log';
}
