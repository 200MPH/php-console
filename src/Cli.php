<?php

/**
 * Abstract CLI
 * Extend this class to create your own CLI program
 * See in to ../examples folder so you will find how to examples
 *
 * @author Wojciech Brozyna <wojciech.brozyna@gmail.com>
 */

namespace phpconsole;

abstract class Cli {
    
    /**
     * Program name. 
     * If not set then class name will be taken as a name
     * @var string
     */
    public $name = '';
    
    /**
     * Program description
     * 
     * @var string
     */
    public $description = '';
    
    /**
     * Average operation time you assume the program may take
     * 
     * @var int Seconds
     */
    public $avgOpTime = 60;
    
    /**
     * Default process file locations.
     * Change accordingly if necessary.
     * @var string
     */
    public $location = '/tmp';
    
    /**
     * Verbose mode
     * If TRUE will output results to the console
     * 
     * @var bool
     */
    protected $verbose = false;
    
    /**
     * Email address for notification
     * 
     * @var string
     */
    protected $email = null;
    
    /**
     * Notification subject (email subject)
     *  
     * @var string
     */
    protected $emailSubject = 'CLI Notification';
    
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
     * Process file.
     * Absolute path.
     * 
     * @var string
     */
    private $processFile = '';
    
    /**
     * @var ReflectionObject
     */
    private $reflection;
    
    /**
     * Execute command for module
     * Do the job you want to do in this function
     * 
     * @return void
     */
    abstract public function run(): void; 
    
    /**
     * Get program options.
     * @return array If no options return an empty array.
     */
    abstract public function getOptions(): array;
    
    /**
    * Render coloured output
    * 
    * @param string $string String to be coloured
    * @param string $foreground_color [optional] Foreground colour code
    * @param string $background_color [optional] Background colour code
    * @param bool $newLine [optional] Set true if you wish attach new line code
    * @param bool $bold [optional] Set font bold
     * 
     * @return void
    */
    static public function render(string $string, 
                                  string $foreground_color = null, 
                                  string $background_color = null, 
                                  bool $newLine = false, 
                                  bool $bold = false): void
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
     * Render standard text output
     * 
     * @param string Text to display
     * @return void
     */
    public function output($string): void
    {
        if($this->verbose === true) {
            print($string);       
        }
        
        $this->saveOutput($string);
    }
    
    /**
     * Render success text output.
     * Display green text in to console output.
     * 
     * @param string Text to display
     * @return void
     */
    public function outputSuccess($string): void
    {
        if($this->verbose === true) {   
            Cli::render($string, CliColors::FG_GREEN, null);
        }
        
        $this->saveOutput($string);
    }
    
    /**
     * Render error text output
     * Display red text in to console output.
     * 
     * @param string Text to display
     * @return void
     */
    public function outputError($string): void
    {
        if($this->verbose === true) {
            Cli::render($string, CliColors::FG_RED, null);
        }
        
        $this->saveOutput($string);
    }
    
    /**
     * Render warning text output
     * Display yellow text in to console output.
     * 
     * @param string Text to display
     * @return void
     */
    public function outputWarning($string): void
    {
        if($this->verbose === true) {
            Cli::render($string, CliColors::FG_YELLOW, null);
        }
        
        $this->saveOutput($string);
    }
    
    /**
     * Write process info
     * 
     * @return string File location
     */
    public function writeProcessFile(): string
    {
        $phpPid = getmypid();
        $this->processFile = $this->location . '/' .$this->getName() . '.' . microtime(true) . '.lock';
        $date = date('Y-m-d H:i:s');
        $content = ['PID' => $phpPid, 
                    'DATE' => $date, 
                    'NAME' => $this->getName(),
                    'DESC' => $this->description, 
                    'FILE' => $this->processFile, 
                    'AVG_OP_TIME' => $this->avgOpTime, 
                    'LOCK' => $this->lock];
        
        file_put_contents($this->processFile, json_encode($content));
        chmod($this->processFile, 0777);
        
        if($this->lock === true) {
            $this->warningOutput("Process {$phpPid} locked at {$date}" . PHP_EOL);
        }
        
        return $this->processFile;   
    }
    
    /**
     * Show help message for program
     * 
     * @return void
     */
    public function showHelpMsg(): void
    {
        foreach($this->getAllOptions() as $option) {
            $optionStr = '-' . $option['shortOption'] . '|--' . $option['longOption'];
            Cli::render($optionStr, CliColors::FG_GREEN, null, true);
            Cli::render($option['description'], CliColors::FG_YELLOW, null, true);
            Cli::render(PHP_EOL);
        }
        
        // terminate if this method is called
        exit();   
    }
    
    /**
     * Find locked process
     * 
     * @return array|false Return process data or FALSE if not found.
     */
    public function findLockedProcess() 
    {
        foreach($this->getProcessFiles() as $file) {
            $process = $this->parseProcessFile($file);
            
            if($process['LOCK'] === true) {
                return $process;
            }
        }
        
        return false;
    }
    
    /**
     * Delete process file
     * 
     * @return void
     */
    public function deleteProcessFile(): void
    {
        $lock = $this->parseProcessFile($this->processFile);  
        
        if(file_exists($this->processFile) === true) {
            unlink($this->processFile);
        }
        
        if($lock['LOCK'] === true) {    
            $this->successOutput("Process {$lock['PID']} unlocked (Locked at {$lock['DATE']})" . PHP_EOL);
        }
    }
    
    /**
     * Set program options
     * 
     * @return void
     */
    public function setOptions(): void
    {
        
        $options = $this->getAllOptions();
        $short = '';
        $longArr = [];
        
        foreach($options as $option) {
            $short .= $option['shortOption'];
            $long = $option['longOption'];
            if($option['hasValue'] === true && $option['requiredValue'] === true) {
                // required value
                $short .= ':';
                $long .= ':';
            } elseif($option['hasValue'] === true) {
                // optional value
                $short .= '::';
                $long .= ':';
            }
            $longArr[] = $long;
        }
        
        $cliOptions = getopt($short, $longArr);
        
        print_r($options);
        print_r($cliOptions);
        
        foreach($options as $option) {
            if(isset($cliOptions[$option['shortOption']]) || isset($cliOptions[$option['longOption']])) {
                $this->{$option['callback']}();
            }
        }
    }
    
    /**
     * Set verbose
     * 
     * @return void
     */
    protected function verbose(): void
    {
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
    }
    
    /**
     * Save output in to file
     * 
     * @param string $string
     * @return void
     */
    final protected function saveOutput($string): void
    {
        if($this->writeOutputFile !== false) {       
            file_put_contents($this->writeOutputFile, $string, FILE_APPEND);
        }
    }
    
    /**
     * Get program all options, default ones and also from child class.
     * 
     * @throw RuntimeException Option method not defined
     * @return array
     */
    private function getAllOptions(): array
    {
        
//        if(method_exists($this, $option['callback']) === true) {
//            ///execute callable method
//            $this->{$option['callback']}();
//        } else {
//            throw new \RuntimeException("Option method {$option['callback']}() doesn't exists", ErrorCodes::OPT_METH_ERR);
//        }
        return array_merge($this->getDefaultOptions(), $this->getOptions());       
    }
    
    /**
     * Get process files.
     * 
     * @return array
     */
    private function getProcessFiles(): array
    {
        $searchPattern = $this->location;
        $searchPattern.= $this->getName();
        $searchPattern.= '.*.*.lock';
        $files = glob($searchPattern);
        
        return is_array($files) ? $files : [];
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
     * Parse file process string
     * 
     * @param string $file
     * @return array
     */
    private function parseProcessFile(string $file): array
    {
        if(file_exists($file) === false) {
            return ['PID' => 0, 
                    'DATE' => '', 
                    'NAME' => '', 
                    'DESC' => '', 
                    'FILE' => '', 
                    'AVG_OP_TIME' => $this->avgOpTime, 
                    'LOCK' => false];
        }
        
        $json = file_get_contents($file);
        $arr = json_decode($json, true);    
                
        return $arr;   
    }
    
    /**
     * Get program name
     * 
     * @return string
     */
    private function getName(): string
    {
        if(empty($this->name) === false) {
            return $this->name;
        } else {
            $this->reflection = new \ReflectionObject($this);
            $this->name = $this->reflection->getShortName();
            return $this->name;
        }
    }
    
    /**
     * Send process lock notification
     * 
     * @return void
     */
    public function sendLockNotification(): void
    {
        
        $lockData = $this->parseProcessFile($this->processFile);    
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
        $msg .= "\t 2. Remove lock file {$this->processFile} (note that file name is various)\n";

        $this->send(Notify::ERROR, $msg);
        
    }
    
    /**
     * Send email notification
     * 
     * @param const $type Notify::SUCCESS | Notify::ERROR | Notify::INFO
     * @param string $message Message to be send. HTML code accepted
     * 
     * @return bool
     */
    protected function send($type, $message): bool
    {
        if(empty($this->email) === false) {       
            $notify = new Notify();
            $notify->setEmail($this->email);
            $notify->setSubject($this->emailSubject);
            $notify->setMessage($message);
            return $notify->send($type);
        }

        return false;        
    }
    
    /**
     * Get program default options
     * 
     * @return array
     */
    private function getDefaultOptions(): array
    {
        
        $options = [];
        $options[] = array('shortOption' => 'h',
                           'longOption' => 'help',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'callback' => 'showHelpMsg', 
                           'description' => 'Display this page');
        
        $options[] = array('shortOption' => 'l',
                           'longOption' => 'lock',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'callback' => 'lock', 
                           'description' => 'Lock module process. Will not let you run another instance of this same module until current is finished. However you can execute script for another module');
        
        $options[] = array('shortOption' => 'v',
                           'longOption' => 'verbose',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'callback' => 'verbose', 
                           'description' => 'Turn on verbose mode');

        $options[] = array('shortOption' => 'w',
                           'longOption' => 'write-output',
                           'hasValue' => true,
                           'requiredValue' => true,
                           'callback' => 'writeOutput', 
                           'description' => "Write output in to file. Eg ./cli 'myNamespace\MyProgram' --write-output=\"/home/user/test.log\"");
        
        return $options;
    }
}
