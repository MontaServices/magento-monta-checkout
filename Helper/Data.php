<?php
namespace Montapacking\MontaCheckout\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;

class Data extends AbstractHelper
{
    /**
     * @var DirectoryList
     */
    protected $directoryList;

    /**
     * @param Context $context
     * @param DirectoryList $directoryList
     */
    public function __construct(
        Context $context,
        DirectoryList $directoryList
    )
    {
        $this->directoryList = $directoryList;
        parent::__construct($context);
    }

    /** Get path to requested directory
     *
     * @param string $directory
     * @return string - absolute system path
     * @throws FileSystemException
     */
    public function getPath($directory = 'log')
    {
        return $this->directoryList->getPath($directory);
    }

    /**
     * @param $path
     * @return array
     */
    protected function getLogFiles($path)
    {
        $list = scandir($path);
        //remove rubbish from array
        array_splice($list, 0, 2);

        $output = [];
        foreach ($list as $file) {
            if (is_dir($path . DIRECTORY_SEPARATOR . $file)) {
                foreach ($this->getLogFiles($path . DIRECTORY_SEPARATOR . $file) as $childFile) {
                    $output[] = $file . DIRECTORY_SEPARATOR . $childFile;
                }
            } else {
                $output[] = $file;
            }
        }

        return $output;
    }

    /**
     * @param $bytes
     * @param $precision
     * @return string
     */
    protected function filesizeToReadableString($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * @return array
     * @throws FileSystemException
     */
    public function buildLogData()
    {
        $maxNumOfLogs = 30;
        $logFileData = [];
        $path = $this->getPath() . DIRECTORY_SEPARATOR;

        //build log data into array
        foreach ($this->getLogFiles($this->getPath()) as $file) {
            $logFileData[$file]['name'] = $file;
            $logFileData[$file]['filesize'] = $this->filesizeToReadableString((filesize($path . $file)));
            $logFileData[$file]['modTime'] = filemtime($path . $file);
            $logFileData[$file]['modTimeLong'] = date("F d Y H:i:s.", filemtime($path . $file));
        }

        //sort array by modified time
        usort($logFileData, function ($item1, $item2) {
            return $item2['modTime'] <=> $item1['modTime'];
        });

        //limit the amount of log data $maxNumOfLogs
        return array_slice($logFileData, 0, $maxNumOfLogs);
    }

    /**
     * @param $fileName
     * @param $numOfLines
     * @return string
     */
    public function getLastLinesOfFile($fileName, $numOfLines)
    {
        $lines = $this->tailExec($fileName, $numOfLines);
        return implode('', $lines);
    }

    /**
     * @param $file
     * @param $lines
     * @return array
     */
    protected function tailExec($file, $lines)
    {
        //global $fsize;
        $handle = fopen($file, "r");
        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = [];
        while ($linecounter > 0) {
            $t = " ";
            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }
                $t = fgetc($handle);
                $pos--;
            }
            $linecounter--;
            if ($beginning) {
                rewind($handle);
            }
            $text[$lines - $linecounter - 1] = fgets($handle);
            if ($beginning) {
                break;
            }
        }
        fclose($handle);
        return array_reverse($text);
    }
}
