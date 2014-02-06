<?php namespace League\PHPUnitCoverageListener;

/**
 * PHPUnit listener interface
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

interface ListenerInterface
{
	const COVERAGE_FILE = 'coverage.xml';
    const COVERAGE_OUTPUT = 'coverage.json';
    const GIT_DIRECTORY = '.git';
    const GIT_HEAD = 'HEAD';

    /**
     * Listener constructor
     *
     * @param array Argument that sent from phpunit.xml
     */
    public function __construct($args = array());

    /**
     * Printer getter
     *
     * @return PrinterInterface
     */
    public function getPrinter();
}