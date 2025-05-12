<?php

namespace Montapacking\MontaCheckout\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\DriverInterface;
use Montapacking\MontaCheckout\Logger\Logger;

class LogCleanup
{


    /**
     * LogCleanup constructor.
     */
    public function __construct(
        protected readonly Logger $logger,
        protected readonly DriverInterface $driver,
        protected readonly DirectoryList $directoryList
    )
    {
    }

    /**
     * @return $this
     */
    public function execute()
    {
        try {
            //Get logfile
            $path = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/log/montapacking_checkout.log';
            $array = explode("\n", $this->driver->fileGetContents($path));

            $line_array = array();
            $d2 = date('Y-m-d', strtotime('-30 days'));
            foreach ($array as $line) {
                if (!str_starts_with($line, '[' . $d2)) {
                    $line_array[] = $line;
                }
            }
            $this->driver->filePutContents($path, implode(PHP_EOL, $line_array));
        } catch (\Exception $e) {
            $this->logger->error("Something went wrong removing logs older than 30 days");
        }
        return $this;
    }
}
