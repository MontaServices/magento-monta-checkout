<?php

namespace Montapacking\MontaCheckout\Helper;

use Montapacking\MontaCheckout\Exception;

/**
 * Trait ReadLogFileTrait
 *
 * @package Montapacking\MontaCheckout\Helper
 */
trait ReadLogFileTrait
{
    /**
     * Fetch block
     *
     * @param string $file
     * @param int $start
     * @param int $limit
     * @return array
     */
    public function fetch($file, $limit = 1, $start = 0): array
    {
        $output = [];
        $block = 0;
        $blockStamp = null;

        $arr = [];
        $count = 0;
        foreach ($this->fileReader()($file) as $line) {
            $count++;
            $arr[$count] = $line;
        }
        $arr = array_reverse($arr);

        foreach ($arr as $line) {
            //match next log file text block by timestamp
            if (preg_match('#^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]#', $line, $matches)) {
                if ($start + $limit < ++$block) {
                    break;
                }
                if ($start + 1 > $block) {
                    continue;
                }

                $blockStamp = $matches[1]; // set new block timestamp
                $i = 0;
                while (isset($output[$blockStamp])) {
                    $blockStamp = $matches[1] . '.' . ++$i;
                }

                $line = str_replace($matches[0] . ' ', '', $line); // cut timestamp out
                $output[$blockStamp] = $line . PHP_EOL;
            } elseif ($output) {
                $output[$blockStamp] .= $line . PHP_EOL;
            }
        }

        return $output;
    }

    /**
     * Get file reader
     *
     * @return \Closure
     */
    private function fileReader(): \Closure
    {
        return function ($file) {
            if (false !== strpos($file, '../')) {
                throw new Exception('LFI protection. Parent directory is prohibited to use.');
            }

            $rs = fopen($file, 'r');

            if (!$rs) {
                throw new Exception('Cannot open file: ' . $file);
            }

            while (($line = fgets($rs)) !== false) {
                yield $line;
            }

            fclose($rs);
        };
    }
}
