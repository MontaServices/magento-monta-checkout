<?php
namespace Montapacking\MontaCheckout\Block;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Montapacking\MontaCheckout\Helper\Data
     */
    protected $logDataHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Montapacking\MontaCheckout\Helper\Data $logDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Montapacking\MontaCheckout\Helper\Data $logDataHelper,
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
