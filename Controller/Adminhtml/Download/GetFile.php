<?php
namespace Montapacking\MontaCheckout\Controller\Adminhtml\Download;

class GetFile extends AbstractLog
{
    /**
     * @param $filename
     * @return string
     */
    protected function getFilePathWithFile($filename)
    {
        return 'var/log/' . $filename;
    }
}
