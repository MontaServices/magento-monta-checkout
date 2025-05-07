<?php
namespace Montapacking\MontaCheckout\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Montapacking\MontaCheckout\Helper\Data;

class Index extends Template
{
    /**
     * @var Data
     */
    protected $logDataHelper;

    /**
     * @param Context $context
     * @param Data $logDataHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $logDataHelper,
        array $data = []
    )
    {
        $this->logDataHelper = $logDataHelper;
        parent::__construct($context, $data);
    }

    public function getLogFiles()
    {
        return $this->logDataHelper->buildLogData();
    }

    public function downloadLogFiles($fileName)
    {
        return $this->getUrl('logviewer/download/getfile', ['file' => $fileName]);
    }

    public function previewLogFile($fileName)
    {
        return $this->getUrl('logviewer/view/index', ['file' => $fileName]);
    }
}
