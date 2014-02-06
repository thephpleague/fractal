<?php namespace League\PHPUnitCoverageListener;

/**
 * PHPUnit printer interface
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

interface PrinterInterface
{

    /**
     * Main output dumper
     *
     * @param  string Output
     * @return stream
     */
    public function out($output = '');

    /**
     * Main output dumper with padding
     *
     * @param  string data
     * @return stream
     */
    public function printOut($output = '');
}