<?php namespace League\PHPUnitCoverageListener\Printer;

use League\PHPUnitCoverageListener\PrinterInterface;

/**
 * Array printer
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class ArrayOut implements PrinterInterface
{
    /**
     * @var array Output array
     */
    public $output = array();

    /**
     *{@inheritdoc}
     */
    public function out($output = '')
    {
        $this->output[] = $output."\n";
    }

    /**
     *{@inheritdoc}
     */
    public function printOut($output = '')
    {
        $this->output[] = str_pad(' ', 2, '*').' '.$output;
    }
}