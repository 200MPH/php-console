<?php

/**
 * Abstract Cli Module
 * Extend this class to create your own module
 * See in to ../examples folder so you will find some interesting solutions
 *
 * @author Wojciech Brozyna <wojciech.brozyna@gmail.com>
 */

namespace phpconsole;

use ReflectionObject;

abstract class Cli {
    
    /**
     * Email address for notification
     * Set email address in your child class if you wish to receive notifications
     * 
     * @var string
     */
    public $email = null;
    
    /**
     * Notification subject (email subject)
     * Set it in you child class
     * 
     * @var string
     */
    public $notificationSubject = 'M-Commander Notification';
    
    /**
     * Description
     */
    public $description = '';
    
    /**
     * Average operation time
     * @var int Seconds
     */
    public $avgOpTime = 60;
        
    /**
     * Args count
     * 
     * @var int
     */
    protected $argc;
    
    /**
     * Cli arguments
     * 
     * @var array
     */
    protected $args = [];
    
    /**
     * Default options
     * 
     * @var array
     */
    protected $defaultOptions = [];
    
    /**
     * Verbose mode
     * If TRUE will output results to the console
     * 
     * @var bool
     */
    protected $verbose = false;
    
    /**
     * Write output file
     * 
     * @var bool|string
     */
    private $writeOutputFile = false;
    
    /**
     * Lock option
     * @var bool
     */
    private $lock = false;
    
    /**
     * Lock folder
     * 
     * @var string
     */
    private $lockFile = '/tmp/';
    
    /**
     * @var ReflectionObject
     */
    private $reflection;
    
    /**
     * Disable email notification
     * 
     * @var bool
     */
    private $notify = true;
    
    /**
     * Execute command for module
     * Do the job you want to do in this function
     * 
     * @return void
     */
    abstract public function execute(); 
    
    /**
     * 
     * @param int $cliArgsCount CLI arguments count
     * @param array $cliArgs CLI arguments
     */
    public function __construct($cliArgsCount, $cliArgs) 
    {
        
        $this->argc = $cliArgsCount;
        $this->args = $cliArgs;
        $this->reflection = new ReflectionObject( $this );
        $this->lockFile .= $this->reflection->getShortName() . '.' . microtime(true) . '.lock';
               
    }
    
    /**
    * 
    * Render coloured output
    * 
    * @param string $string String to be coloured
    * @param const $foreground_color [optional] Foreground colour code
    * @param const $background_color [optional] Background colour code
    * @param bool $newLine [optional] Set true if you wish attach end line code '\n'
    * @param bool $bold [optional] Set font bold
    */
    static public function render($string, $foreground_color = null, $background_color = null, $newLine = false, $bold = false) 
    {
        $bolder = 0;
        $colored_string = "";

        // Check if given foreground color found
        if (isset($foreground_color)) {
            if ($bold === true) {
                $bolder = 1;
            }
            $colored_string .= "\033[" . $bolder . $foreground_color . "m";
        }
        
        // Check if given background color found
        if (isset($background_color)) {
            $colored_string .= "\033[" . $background_color . "m";
        }

        // Add string and close coloring
        $colored_string .= $string . "\033[0m";

        if ($newLine === true) {
            print($colored_string . PHP_EOL);
        } else {
            print $colored_string;
        }
    }

    /**
     * Render standard gray text output
     * 
     * @param string Text to display
     * @return void
     */
    public function output($string)
    {
        
        if($this->verbose === true) {
            
            print($string);
            
        }
        
        $this->saveOutput($string);
        
    }
    
    /**
     * Render success text output
     * Display green text in to console output.
     * For more colors please use CliColors::render()
     * 
     * @param string Text to display
     * @return void
     */
    public function successOutput($string)
    {
        
        if($this->verbose === true) {
        
            CliColors::render($string, CliColors::FG_GREEN, null);
        
        }
        
        $this->saveOutput($string);
        
    }
    
    /**
     * Render error text output
     * Display red text in to console output.
     * For more colors please use CliColors::render()
     * 
     * @param string Text to display
     * @return void
     */
    public function errorOutput($string)
    {
        
        if($this->verbose === true) {
            
            CliColors::render($string, CliColors::FG_RED, null);
            
        }
        
        $this->saveOutput($string);
        
    }
    
    /**
     * Render warning text output
     * Display yellow text in to console output.
     * For more colors please use CliColors::render()
     * 
     * @param string Text to display
     * @return void
     */
    public function warningOutput($string)
    {
        
        if($this->verbose === true) {
            
            CliColors::render($string, CliColors::FG_YELLOW, null);
            
        }
        
        $this->saveOutput($string);
        
    }
    
    /**
     * Send email notification
     * 
     * @param const $type Notify::SUCCESS | Notify::ERROR | Notify::INFO
     * @param string $message Message to be send. HTML code accepted
     * 
     * @return bool
     */
    public function notify($type, $message)
    {
        
        if($this->notify === true && empty($this->email) === false) {
            
            $notify = new Notify();
            
            $notify->setEmail($this->email);
            
            $notify->setSubject($this->notificationSubject);
            
            $notify->setMessage($message);
            
            return $notify->send($type);
            
        }
        
        return false;
        
    }
    
    /**
     * Write lock info
     * 
     * @return string File location
     */
    public function writeLockInfo()
    {
        
        $phpPid = getmypid();
        $date = date('Y-m-d H:i:s');
        $name = $this->reflection->getShortName();
        
        $content = ['PID' => $phpPid, 
                    'DATE' => $date, 
                    'NAME' => $name, 
                    'DESC' => $this->description, 
                    'FILE' => $this->lockFile, 
                    'AVG_OP_TIME' => $this->avgOpTime, 
                    'LOCK' => $this->lock];
        
        file_put_contents($this->lockFile, json_encode($content));
        chmod($this->lockFile, 0777);
        
        return $this->lockFile;
        
    }
    
    /**
     * Delete process
     * 
     * @param string $file
     * @return bool
     */
    public function deleteProcFile(string $file) 
    {
        
        if(file_exists($file) === true) {
            return unlink($file);
        }
        
        return true;
        
    }
    
    /**
     * Show help message for module
     * 
     * @return void
     */
    public function helpMsg()
    {
        
        foreach($this->defaultOptions as $array) {
            
            foreach($array['options'] as $option) {
                
                CliColors::render($option . PHP_EOL, CliColors::FG_GREEN);
                
            }
            
            print("\t\t\t");
            
            CliColors::render($array['description'] . PHP_EOL, CliColors::FG_YELLOW);
            
        }
        
        exit();
        
    }
    
    /**
     * Set verbose
     * 
     * @return void
     */
    protected function verbose()
    {
        
        print("Verbose mode ");
        
        CliColors::render("ON", CliColors::FG_GREEN, null, true);
        
        $this->verbose = true;
        
    }
    
    /**
     * Write output into file
     * 
     * @return void
     */
    protected function writeOutput()
    {
        
        foreach($this->args as $key => $value) {
            
            if($value === '-w' || $value === '--write-output') {
                
                $this->isPathNameProvided($key);
                    
                $this->isFileWritable($key);
                
                break;
                
            }
            
        }
        
    }
    
    /**
     * Lock current process
     * 
     * @return void
     */
    protected function lock()
    {
        
        $this->lock = true;
        $phpPid = getmypid();
        $date = date('Y-m-d H:i:s');
        $this->warningOutput("Process {$phpPid} locked at {$date}" . PHP_EOL);
                
    }
    
    /**
     * Disable notification
     * 
     * @return void
     */
    protected function disableNotification()
    {
        
        $this->notify = false;
        
        $this->output('Notification ');
        $this->warningOutput('DISABLED' . PHP_EOL);
        
    }
    
    /**
     * Disable error notification
     * 
     * @return void
     */
    protected function disableErrors()
    {
        
        ini_set('display_errors', '0');
        
        $this->output('Error output ');
        $this->warningOutput('DISABLED' . PHP_EOL);
        
    }
    
    /**
     * Check if process is locked, if so will send lock e-mail notification
     * 
     * @return array|false
     */
    public function isLocked()
    {
        
        $searchPattern = '/tmp/';
        $searchPattern.= $this->reflection->getShortName();
        $searchPattern.= '.*.*.lock';
        $files = glob($searchPattern);
        
        if(empty($files) === true) {
            return false;
        }
        
        $this->lockFile = end($files);
        $lock = $this->parseLockString();
        
        if($lock['LOCK'] === true) {
            $this->lockNotify();
            return $lock;
        } else {
            return false;
        }
        
    }
    
    /**
     * Unlock process
     * 
     * @return void
     */
    public function unlock()
    {
        
        $lock = $this->parseLockString();
        
        if(file_exists($this->lockFile) === true) {
            unlink($this->lockFile);
        }
        
        if($lock['LOCK'] === true) {    
            $this->successOutput("Process {$lock['PID']} unlocked (Locked at {$lock['DATE']})" . PHP_EOL);
        }
        
    }
    
    /**
     * Load default options
     * 
     * @return void
     */
    protected function loadOptions()
    {
        
        $this->defaultOptions[] = array('options' => array('--disable-errors'), 
                                        'callback' => 'disableErrors', 
                                        'description' => 'Disable errors notification');
        
        $this->defaultOptions[] = array('options' => array('--disable-notification'), 
                                        'callback' => 'disableNotification', 
                                        'description' => 'Disable email notification');
        
        $this->defaultOptions[] = array('options' => array('-h', '--help'), 
                                        'callback' => 'helpMsg', 
                                        'description' => 'Display this page');
        
        $this->defaultOptions[] = array('options' => array('-l', '--lock'), 
                                        'callback' => 'lock', 
                                        'description' => 'Lock module process. Will not let you run another instance of this same module until current is finished. However you can execute script for another module.');
        
        $this->defaultOptions[] = array('options' => array('-v', '--verbose'), 
                                        'callback' => 'verbose', 
                                        'description' => 'Verbose mode');
        
        $this->defaultOptions[] = array('options' => array('-w', '--write-output'), 
                                        'callback' => 'writeOutput', 
                                        'description' => "Write output in to file. Eg ./m-commander 'myNamespace\MyModule' -w /home/user/test.log");
        
    }
    
    /**
     * Save output in to file
     * 
     * @param string $string
     */
    final protected function saveOutput($string)
    {
        
        if($this->writeOutputFile !== false) {
            
            file_put_contents($this->writeOutputFile, $string, FILE_APPEND);
            
        }
        
    }
    
    /**
     * Setup internal arguments options
     * Each module might have different options.
     * 
     * @return void
     */
    final public function setupOptions()
    {
       
        $this->loadOptions();
        
        foreach ($this->args as $k => $value) {
            
            //first arg is script name and path to module, we don't need it here
            if($k <= 1) { continue; }
                        
            if(strpos($value, '-') === 0 || strpos($value, '--') === 0) {
                
                $this->loadInternalOption($value);
                
            }
            
        }
                
    }
    
    /**
     * Execute internal option
     * 
     * @param string $value Option value
     * @throw RuntimeException Invalid argument
     */
    final private function loadInternalOption($value)
    {
        
        foreach($this->defaultOptions as $array) {
            
            if(in_array($value, $array['options']) === false) {
                
                continue;
                
            }
                
            if(method_exists($this, $array['callback'])) {

                ///execute callable method
                $this->{$array['callback']}();

                return 0;

            } else {

                throw new \RuntimeException("Option method doesn't exists", CliCodes::OPT_METH_ERR);

            }
            
        }
        
        throw new \RuntimeException("Invalid argument: {$value} \nTry -h or --help to see all available options", CliCodes::OPT_FAIL);
        
    }
    
    /**
     * Check if path is provided for -w|--write-output option
     * 
     * @var int $optionLocation Expected -w option location in ARGS array.
     * In another word, ARGS array key where -w|--write-output option occured
     * 
     * @throw RuntimeException
     */
    final private function isPathNameProvided($optionLocation)
    {
        
        // next to argument should be file name
        $pathLocation = $optionLocation + 1;
       
        if(isset( $this->args[$pathLocation] ) === false) {

            throw new \RuntimeException('You have to specify path to the file for -w|--write-output option', CliCodes::OPT_WRITE_NO_FILE);

        }
        
    }
    
    /**
     * Check if file is writable
     * 
     * @var int $optionLocation Expected -w option location in ARGS array.
     * In another word, ARGS array key where -w|--write-output option occured
     * 
     * @throw RuntimeException
     */
    final private function isFileWritable($optionLocation)
    {
        
        // next to argument should be file name
        $pathLocation = $optionLocation + 1;
        
        $this->writeOutputFile = $this->args[$pathLocation];

        if(@file_put_contents($this->writeOutputFile, '') === false) {

            // looks like we can't create the file
            throw new \RuntimeException('File is not writable. Check filename and permissions', CliCodes::OPT_FILE_PER_ERR);

        }
        
    }
    
    /**
     * Parse lock string
     * 
     * @return array
     */
    final private function parseLockString()
    {
        
        if(file_exists($this->lockFile) === false) {
            return ['PID' => 0, 
                    'DATE' => '', 
                    'NAME' => '', 
                    'DESC' => '', 
                    'FILE' => '', 
                    'AVG_OP_TIME' => $this->avgOpTime, 
                    'LOCK' => $this->lock];
        }
        
        $json = file_get_contents($this->lockFile);
        $arr = json_decode($json, true);    
                
        return $arr;
        
    }
    
    /**
     * Send email notification
     * 
     * @return void
     */
    final private function lockNotify()
    {
        
        $lockData = $this->parseLockString();    
        $this->notificationSubject = "Process #{$lockData['PID']} locked!";

        $msg = "PID: <strong>{$lockData['PID']}</strong> \n";
        $msg .= "Name: <strong>{$lockData['NAME']}</strong> \n";
        $msg .= "Description: <strong>{$lockData['DESC']}</strong> \n";
        $msg .= "Locked at: <strong>{$lockData['DATE']}</strong> \n\n";
        $msg .= "This is happens when: \n";
        $msg .= "\t 1. Current process is not finished yet and another instance of the same job is started. \n";
        $msg .= "\t 2. Previous instance crashed and left lock file. \n\n";
        $msg .= "Unlock instructions: \n";
        $msg .= "\t 1. Make sure that unlock process is safe\n";
        $msg .= "\t 2. Remove lock file {$this->lockFile} (note that file name is various)\n";

        $this->notify(Notify::ERROR, $msg);
        
    }
}
