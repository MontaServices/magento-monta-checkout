<?php
namespace Montapacking\MontaCheckout\Controller\Adminhtml\Download;

use Laminas\Filter\BaseName;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Controller\Adminhtml\System;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NotFoundException;

abstract class AbstractLog extends System
{
    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param FileFactory $fileFactory
     */
    public function __construct(Context $context, FileFactory $fileFactory)
    {
        $this->fileFactory = $fileFactory;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        $filePath = $this->getFilePathWithFile($this->getRequest()->getParam('file'));

        $filter = new BaseName();
        $fileName = $filter->filter($filePath);
        try {
            return $this->fileFactory->create(
                $fileName,
                [
                    'type' => 'filename',
                    'value' => $filePath
                ]
            );
        } catch (\Exception $e) {
            throw new NotFoundException(__($e->getMessage()));
        }
    }

    /**
     * @param $filename
     * @return string
     */
    abstract protected function getFilePathWithFile($filename);
}
