<?php

namespace Montapacking\MontaCheckout\Block\View;

use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Montapacking\MontaCheckout\Helper\Data;
use Montapacking\MontaCheckout\Helper\ReadLogFileTrait;

class Index extends Template
{
    use ReadLogFileTrait {
        fetch as fetchLogFileBlocks;
    }

    /**
     * @var Data
     */
    protected $logDataHelper;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Montapacking\MontaCheckout\Helper\Data $logDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Montapacking\MontaCheckout\Helper\Data $logDataHelper,
        array $data = [],
        Request $request = null
    )
    {
        $this->request = $request;
        $this->logDataHelper = $logDataHelper;
        parent::__construct($context, $data);
        if ($this->request != null) {
            $params = $this->request->getParams();

            if (isset($params['clear'])) {
                $file_name = $this->logDataHelper->getPath() . DIRECTORY_SEPARATOR . $this->getFileName();

                if (file_exists($file_name)) {
                    file_put_contents($file_name, '');
                }
            }
        }
    }

    public function getLogFile()
    {
        return $this->logDataHelper->getLastLinesOfFile($this->getFileName(), 10);
    }

    /**
     * Get logs
     *
     * @return array
     */
    public function getLogFileBlocks(): array
    {
        return $this->fetchLogFileBlocks($this->logFile(), $this->getLimit(), $this->getStart());
    }

    public function getLimit(): int
    {
        return (int)$this->getRequest()->getParam('limit', 100) ?: 100;
    }

    public function getStart(): int
    {
        return (int)$this->getRequest()->getParam('start', 0);
    }

    public function getFileName()
    {
        return $this->getRequest()->getParam('file');
    }

    /**
     * Get limit URL
     *
     * @param int $limit
     * @return string
     */
    public function getLimitUrl(int $limit): string
    {
        return $this->getUrl('*/*/*', [
            '_current' => true,
            'limit' => $limit,
            'file' => $this->getFileName(),
        ]);
    }

    /**
     * Get start URL
     *
     * @param int $start
     * @return string
     */
    public function getStartUrl(int $start): string
    {
        return $this->getUrl('*/*/*', [
            '_current' => true,
            'start' => $start,
            'file' => $this->getFileName(),
        ]);
    }

    /**
     * Get clear URL
     *
     * @param int $start
     * @return string
     */
    public function getClearUrl(): string
    {
        return $this->getUrl('*/*/*', [
            '_current' => false,
            'file' => $this->getFileName(),
        ]);
    }

    /**
     * Get back URL
     *
     * @return string
     */
    public function getBackUrl(): string
    {
        return $this->getUrl('*/grid/', ['_current' => true]);
    }

    /**
     * Get starts list
     *
     * @param int $max
     * @return array
     */
    public function getStarts($max = 10)
    {
        $start = $this->getStart() - $this->getLimit() * 2;
        $start = $start > 0 ? $start : 0;
        if ($start > $this->getLimit() * 3) {
            $step = ceil($start / 4);
            $step -= $step % $this->getLimit();

            return array_merge(
                range(0, $start - $this->getLimit(), $step),
                range($start, $this->getLimit() * ($max - 1) + $start, $this->getLimit())
            );
        }

        return range(0, $this->getLimit() * ($max - 1) + $start, $this->getLimit());
    }

    /**
     * Get starts list
     *
     * @return array
     */
    public function getLimits()
    {
        return [10, 20, 30, 50, 100, 500, 1000];
    }

    /**
     * Get full path to log file
     *
     * @return string
     */
    private function logFile(): string
    {
        return $this->logDataHelper->getPath() . DIRECTORY_SEPARATOR . $this->getFileName();
    }

    public function countLines()
    {
        $file = $this->logFile();
        $linecount = 0;
        $handle = fopen($file, "r");
        while (!feof($handle)) {
            $line = fgets($handle);
            $linecount++;
        }

        fclose($handle);

        return $linecount;
    }
}
