<?php

namespace Montapacking\MontaCheckout\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Montapacking\MontaCheckout\Logger\Logger;

class LogCleanup
{

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var DriverInterface
     */
    protected $_driver;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_directoryList;

    /**
     * LogCleanup constructor.
     */
    public function __construct(
        Logger $logger,
        DriverInterface $driver,
        DirectoryList $directoryList
    )
    {
        $this->_logger = $logger;
        $this->_driver = $driver;
        $this->_directoryList = $directoryList;
    }

    public function execute()
    {
        try {
            //Get logfile
            $path = $this->_directoryList->getPath(DirectoryList::VAR_DIR) . '/log/montapacking_checkout.log';
            $array = explode("\n", $this->_driver->fileGetContents($path));

            $line_array = array();
            $d2 = date('Y-m-d', strtotime('-30 days'));
            foreach ($array as $line) {
                if (!str_starts_with($line, '[' . $d2)) {
                    $line_array[] = $line;
                }
            }
            $this->_driver->filePutContents($path, implode(PHP_EOL, $line_array));
        } catch (\Exception $e) {
            $this->_logger->error("Something went wrong removing logs older than 30 days");
        }
        return $this;
    }
}
