<?php
namespace Montapacking\MontaCheckout\Controller\Adminhtml\Grid;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        protected readonly PageFactory $resultPageFactory
    )
    {
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('Magento_Backend::system');
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Log Viewer'));
        $this->_view->renderLayout();
    }
}
