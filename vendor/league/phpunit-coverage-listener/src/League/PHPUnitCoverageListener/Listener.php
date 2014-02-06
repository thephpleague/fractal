<?php namespace League\PHPUnitCoverageListener;

use League\PHPUnitCoverageListener\ListenerInterface;
use League\PHPUnitCoverageListener\PrinterInterface;
use League\PHPUnitCoverageListener\HookInterface;
use League\PHPUnitCoverageListener\Collection;
use Symfony\Component\Yaml\Yaml;
use \SimpleXMLElement;

/**
 * Main PHPUnit listener class
 *
 * @package  League\PHPUnitCoverageListener
 * @author   Taufan Aditya <toopay@taufanaditya.com>
 */

class Listener implements ListenerInterface
{
    /**
     * @var string
     */
    protected $directory;

	/**
	 * @var PrinterInterface
	 */
	protected $printer;

	/**
	 * @var HookInterface
	 */
	protected $hook;

    /**
     * Listener constructor
     *
     * @param array Argument that sent from phpunit.xml
     * @param bool Boot flag 
     */
    public function __construct($args = array(), $boot = true)
    {
        // Get printer
        $this->ensurePrinter($args);

    	$this->printer = $args['printer'];

        // Get directory
        $this->directory = (isset($_SERVER['PWD'])) ? realpath($_SERVER['PWD']) : getcwd();

        // Register the method to collect code-coverage information
        if ($boot && ($listener = $this)) register_shutdown_function(function() use ($args, $listener) { $listener->handle($args); });
    }

    /**
     * Main handler
     *
     * @param array
     */
    public function handle($args)
    {
        // Starting point!
        $this->printer->out("\n\n".'Collecting CodeCoverage information...');

        // Just collect or also send?
        if (array_key_exists('send', $args) && $args['send'] == false) {
            // In some point we may only want to generate the payload
            // so if 'send' parameter exists and set to false we'll only
            // collect and write code-coverage payload
            $this->collectAndWriteCoverage($args);
        } else {
            // Default is to collect and send
            $this->collectAndSendCoverage($args);
        }

        // Done
        $this->printer->out('Done.');
    }

    /**
     * Printer getter
     *
     * @return PrinterInterface
     */
    public function getPrinter()
    {
    	return $this->printer;
    }

    /**
     * Directory getter
     *
     * @return Path from which the script runs
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Main api for collecting code-coverage information and write it into json payload
     *
     * @param array 
     */
    public function collectAndWriteCoverage($args)
    {
        if ($this->valid($args)) {
            extract($args);

            // Check for exist and valid hook
            if (isset($hook) && $hook instanceof HookInterface) {
                $this->hook = $hook;
                unset($hook);
            }

            // Get the realpath coverage directory
            $coverage_dir = realpath($coverage_dir);
            $coverage_file = $coverage_dir.DIRECTORY_SEPARATOR.self::COVERAGE_FILE;
            $coverage_output = $coverage_dir.DIRECTORY_SEPARATOR.self::COVERAGE_OUTPUT;

            // Get the coverage information
            if (is_dir($coverage_dir) && is_file($coverage_file)) {
                // Build the coverage xml object
                $xml = file_get_contents($coverage_file);
                $coverage = new SimpleXMLElement($xml);

                // Prepare the coveralls payload
                $data = $this->collect($coverage, $args);

                // Write the coverage output
                $this->printer->out('Writing coverage output...');
                file_put_contents($coverage_output, json_encode($data->all(), JSON_NUMERIC_CHECK));
            }
        }
    }

    /**
     * Main api for collecting code-coverage information
     *
     * @param array Contains repo secret hash, target url, coverage directory and optional Namespace
     */
    public function collectAndSendCoverage($args)
    {
        // Collect and write out the data
        $this->collectAndWriteCoverage($args);

        if ($this->valid($args)) {
            extract($args);

            // Get the realpath coverage directory
            $coverage_dir = realpath($coverage_dir);
            $coverage_output = $coverage_dir.DIRECTORY_SEPARATOR.self::COVERAGE_OUTPUT;

            // Send it!
            $this->printer->out('Sending coverage output...');


            // Workaround for cURL create file
            if (function_exists('curl_file_create')) {
                $payload = curl_file_create('json_file', 'application/json', $coverage_output);
            } else {
                $payload = array('json_file'=>'@'.$coverage_output);
            }

            $ch = curl_init(); 
            curl_setopt($ch, CURLOPT_URL, $target_url); 
            curl_setopt($ch, CURLOPT_POST,1); 
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

            // Save output into output buffer
            ob_start();
            $result = curl_exec ($ch); 
            $curlOutput = ob_get_contents();
            ob_end_clean();

            curl_close ($ch); 
            $this->printer->printOut('cURL Output:'.$curlOutput); 
            $this->printer->printOut('cURL Result:'.$result);
        }
    }

    /**
     * Argument validator
     *
     * @param array
     * @return bool
     */
    protected function valid($args = array())
    {
         return array_key_exists('repo_token', $args) 
            && array_key_exists('target_url', $args)
            && array_key_exists('coverage_dir', $args)
            && array_key_exists('namespace', $args);
    }

    /**
     * Printer validator
     *
     * @param array
     * @throws RuntimeException
     */
    protected function ensurePrinter($args)
    {
        if ( ! isset($args['printer'])) {
            throw new \RuntimeException('Printer class not found');
        }


        if ( ! $args['printer'] instanceof PrinterInterface) {
            throw new \RuntimeException('Invalid printer class');
        }
    }

    /**
     * Main collector method
     *
     * @param SimpleXMLElement Coverage report from PHPUnit
     * @param array
     * @return Collection
     */
    protected function collect(SimpleXMLElement $coverage, $args = array())
    {
    	extract($args);

    	$data = new Collection(array(
            'repo_token' => $repo_token,
            'source_files' => array(),
            'run_at' => gmdate('Y-m-d H:i:s -0000'),
            'git' => $this->collectFromGit()->all(),
        ));

 		// Before collect hook
     	if ( ! empty($this->hook)) {
     		$data = $this->hook->beforeCollect($data);
     	}

        // Prepare temporary source_files holder
        $sourceArray = new Collection();

        if (count($coverage->project->package) > 0) {
            // Iterate over the package
            foreach ($coverage->project->package as $package) {
                // Then itterate on each package file
                foreach ($package->file as $packageFile) {
                    $this->printer->printOut('Checking:'.$packageFile['name']);

                    $sourceArray->add(array(
                        md5($packageFile['name']) => $this->collectFromFile($packageFile, $namespace)
                    ));
                }
            }
        }

        // In case the files are not using any namespace at all...
        // @codeCoverageIgnoreStart
        if (count($coverage->project->file) > 0) {
            // itterate over the files
            foreach ($coverage->project->file as $file) {
                $this->printer->printOut('Checking:'.$file['name']);

                $sourceArray->add(array(
                    md5($file['name']) => $this->collectFromFile($file, $namespace)
                ));
            }
        }
        // @codeCoverageIgnoreEnd

        // Last, pass the source information it it contains any information
        if ($sourceArray->count() > 0) {
            $data->set('source_files', array_values($sourceArray->all()));
        }

 		// After collect hook
        if ( ! empty($this->hook)) {
     		$data = $this->hook->afterCollect($data);
     	}

     	return $data;
    }

    /**
     * Collect code-coverage information from a file
     *
     * @param SimpleXMLElement contains coverage information
     * @param string Optional file namespace identifier
     * @throws RuntimeException
     * @return array contains code-coverage data with keys as follow : name, source, coverage
     */
    protected function collectFromFile(SimpleXMLElement $file, $namespace = '')
    {
        // Validate
        if ( ! is_file($file['name'])) throw new \RuntimeException('Invalid '.self::COVERAGE_FILE.' file');

        // Get current dir
        $currentDir = $this->getDirectory();

        // Initial return values
        $name = '';
        $source = '';
        $coverage = array();

        // #1 Get the relative file name
        $pathComponents = explode($currentDir, $file['name']);
        $relativeName = count($pathComponents) == 2 ? $pathComponents[1] : current($pathComponents);
        $name = trim($relativeName, DIRECTORY_SEPARATOR);

        if ( ! empty($namespace)) {
            // Replace backslash with directory separator
            $ns = str_replace('\\', DIRECTORY_SEPARATOR, $namespace);
            $nsComponents = explode($ns, $relativeName);
            $namespacedName = count($nsComponents) == 2 ? $nsComponents[1] : current($nsComponents);

            $name = count($nsComponents) == 2 ? $ns.DIRECTORY_SEPARATOR.trim($namespacedName, DIRECTORY_SEPARATOR) : $namespacedName;
        }

        // Then, we will overwrite any coverage block into it!
        if (count($file->line) > 1) {
            // #2 Build coverage data and the source code
            $count = 0;
            $handle = fopen($file['name'], "r");
            while(!feof($handle)){
                $source .= fgets($handle);
                $count++;
            }

            fclose($handle);

            // Here we build the default coverage values
            $coverage = array_fill(0, $count, null);

            foreach ($file->line as $line) {
                $attributes = current($line->attributes());

                // Only stmt would be count
                if (isset($attributes['type']) 
                    && isset($attributes['count']) 
                    && $attributes['type'] === 'stmt') {

                    // Decrease the line number by one
                    // since key 0 (within coverage array) is actually line number 1
                    $num = (int) $attributes['num'] - 1;

                    // Ensure it match count boundaries
                    if ($num > 0 && $num <= $count) {
                        $coverage[$num] = (int) $attributes['count'];
                    }
                }
            }
        }

        return compact('name', 'source', 'coverage');
    }

    /**
     * Collect git information
     *
     * @return Collection
     */
    public function collectFromGit()
    {
        // Initial git data
        $git = new Collection();

        $gitDirectory = $this->getDirectory().DIRECTORY_SEPARATOR.self::GIT_DIRECTORY;

        if (is_dir($gitDirectory)) {
            // Get refs info from HEAD
            $branch = '';
            $head = Yaml::parse($gitDirectory.DIRECTORY_SEPARATOR.self::GIT_HEAD);

            // @codeCoverageIgnoreStart
            if (is_array($head) && array_key_exists('ref', $head)) {
                $ref = $head['ref'];
                $r = explode('/', $ref);
                $branch = array_pop($r);
            } 
            // @codeCoverageIgnoreEnd

            // Assign branch information
            $git->set('branch', $branch);

            // Get log information
            $logRaw = self::execute('cd '.$this->getDirectory().';git log -1');
            $idRaw = $logRaw[0];
            $authorRaw = $logRaw[1];

            // Build head information
            if (strpos($authorRaw, '<') !== false) {
                 list($author, $email) = explode('<', str_replace('Author:', '', $authorRaw));

                $id = trim(str_replace('commit', '', $idRaw));
                $author_name = $committer_name = trim($author);
                $author_email = $committer_email = trim($email, '>');
                $message = $logRaw[4].(isset($logRaw[5]) ? '...' : '');
            }
           

            // Assign Head information
            $git->set('head', compact('id', 'author_name', 'author_email', 
                            'committer_name', 'committer_email', 'message'));

            // Get remotes information
            $remotes = array();
            $configRaw = self::execute('cd '.$this->getDirectory().';git config --local -l');
            array_walk($configRaw,function($v) use(&$remotes)
            {
                if (0 === strpos($v, 'remote')) {
                    list($key, $prop) = explode('=', $v);
                    $k = explode('.', $key);
                    $attribute = array_pop($k);
                    $name = array_pop($k);
                    $remotes[$name]['name'] = $name;
                    $remotes[$name][$attribute] = $prop;
                }
            });

            // Assign Remotes information
            $git->set('remotes', array_values($remotes));
        }

        return $git;
    }

    /**
     * Execute a command and parse the output as array
     *
     * @param string 
     * @return array 
     */
    protected static function execute($command)
    {
        $res = array();

        ob_start();
        passthru($command, $success);
        $output = ob_get_clean();

        foreach ((explode("\n", $output)) as $line) $res[] = trim($line);

        return array_filter($res);
    }
}