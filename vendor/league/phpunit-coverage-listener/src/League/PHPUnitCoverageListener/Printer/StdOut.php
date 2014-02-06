<?php namespace League\PHPUnitCoverageListener\Printer;

use League\PHPUnitCoverageListener\PrinterInterface;

/**
 * StdOut printer
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class StdOut implements PrinterInterface
{
	/**
     *{@inheritdoc}
     */
    public function out($output = '')
    {
    	fwrite(STDOUT, $output."\n");
    }

    /**
     *{@inheritdoc}
     */
    public function printOut($output = '')
    {
    	fwrite(STDOUT, str_pad(' ', 2, '*').' '.$output.PHP_EOL);
    }
}