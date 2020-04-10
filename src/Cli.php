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
     * Arguments
     * @var array
     */
    private $arguments = [];
    
    /**
     * Execute your program.
     * Do the job you want to do in this function.
     * 
     * @return void
     */
    abstract public function run(): void; 
    
    /**
     * Get program options.
     * If no options in your program then return an empty array.
     * 
     * Return a list (multi dimensional array) of options.
     * Each option is an array and must contain following keys:
     * - shortOption - short option name
     * - longOption - long option name
     * - description - option description
     * - hasValue - TRUE if option may contain a value (optional value), otherwise FALSE 
     * - requiredValue - TRUE if option required value, otherwise FALSE 
     * - isMandatory - TRUE if option and value must be provided, otherwise FALSE
     * - callback - The method name which process this option. Method must have protected access. 
     * 
     * @return array 
     */
    abstract public function getOptions(): array;
    
    /**
    * Render coloured output
    * 
    * @param string $string String to be coloured
    * @param string $foregroundColor [optional] Foreground colour code
    * @param string $backgroundColor [optional] Background colour code
    * @param bool $newLine [optional] Set true if you wish attach new line code
    * @param bool $bold [optional] Set font bold
     * 
     * @return void
    */
    static public function render(string $string, 
                                  string $foregroundColor = null, 
                                  string $backgroundColor = null, 
                                  bool $newLine = false, 
                                  bool $bold = false): void
    {
        $bolder = $bold === true ? "\033[1m" : '';
        $coloredString = "";

        // Check if given foreground color found
        if (isset($foregroundColor)) {
            $coloredString .= "\033[" . $foregroundColor . "m";
        }
        
        // Check if given background color found
        if (isset($backgroundColor)) {
            $coloredString .= "\033[" . $backgroundColor . "m";
        }

        // Add string and close coloring
        $coloredString .= $bolder . $string . "\033[0m";

        if ($newLine === true) {
            print($coloredString . PHP_EOL);
        } else {
            print $coloredString;
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
            $this->successOutput("Process {$lock['PID']} unlocked @ " . date('Y-m-d H:i:s') . PHP_EOL);
            $this->successOutput("Locked at {$lock['DATE']})" . PHP_EOL);
        }
    }
    
    /**
     * Set program options
     * 
     * @return void
     * @throw RuntimeException
     */
    public function setOptions(): void
    {
        
        $options = $this->getAllOptions();
        $shortOptions = $this->getShortOptions();
        $longOptions = $this->getLongOptions();        
        $cliOptions = getopt($shortOptions, $longOptions);
        
        foreach($options as $option) {
            
            $value = false;
            $isset = false;
            
            if(isset($cliOptions[$option['shortOption']])) {
                $value = $cliOptions[$option['shortOption']];
                $isset = true;
            } else if(isset($cliOptions[$option['longOption']])) {
                $value = $cliOptions[$option['longOption']];
                $isset = true;
            } 
            
            // check wether option must be set
            if($option['isMandatory'] === true && $value === false) {
                throw new \RuntimeException("Option -{$option['shortOption']}|--{$option['longOption']} and value must be set.", ErrorCodes::OPT_FAIL);
            }
                
            // check whether option require a value
            if($isset === true) {
           
                if($value === false && $option['hasValue'] === true && $option['requiredValue'] === true) {
                    throw new \RuntimeException("Option -{$option['shortOption']}|--{$option['longOption']} require a value.", ErrorCodes::OPT_FAIL);
                }
                
                // execute
                $this->{$option['callback']}($value);
            }
            
        }
    }
    
    /**
     * Get Arguments
     * 
     * @return array
     */
    public function getArguments(): array 
    {
        // remove first element
        array_shift($this->arguments);
        
        // remove last element
        array_pop($this->arguments);
        
        // remove '-' and '--' signs
        array_walk($this->arguments, function(&$value) {
            $value = str_replace(['-', '--'], '', $value);
        });
        
        return $this->arguments;
    }
    
    /**
     * Set arguments
     * @param array
     * @return void
     */
    public function setArguments(array $arguments): void 
    {
        $this->arguments = $arguments;
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
     * @param string $file Absolute path
     * @return void
     */
    protected function writeOutput(string $file): void
    {
        
        $this->output('Write output to: ');
        $this->outputWarning($file . PHP_EOL);
        
        if(empty($file)) {
            throw new \RuntimeException('You have to specify path to the file Eg. --write="/path/to/file"', ErrorCodes::OPT_WRITE_NO_FILE);
        }
        
        $this->writeOutputFile = $file;
        
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
     * Set email
     * 
     * @param string $email
     * @return Cli
     */
    protected function setEmail(string $email): Cli 
    {
        $this->output('Email set to:' . $email . PHP_EOL);
        $this->email = $email;
        
        return $this;
    }
    
    /**
     * Set subject
     * 
     * @param string $subject
     * @return Cli
     */
    protected function setSubject(string $subject): Cli 
    {
        $this->output('Subject set to:' . $subject . PHP_EOL);
        $this->emailSubject = $subject;
        
        return $this;
    }
    
    /**
     * Set temporary location
     * 
     * @param string $location
     * @return Cli
     */
    protected function setTmpLocation(string $location): Cli 
    {
        $this->output('Temporary location set to:' . $location . PHP_EOL);
        $this->location = $location;
        
        return $this;
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
     * Get program all options, default ones and also from child class.
     * 
     * @throw RuntimeException
     * @return array
     */
    private function getAllOptions(): array
    {
        
        $checks = [];
        $options = array_merge($this->getDefaultOptions(), $this->getOptions());
        
        foreach ($options as $option) {
            
            if(in_array($option['shortOption'], $checks) === true) {
                throw new \RuntimeException("Short option {$option['shortOption']} reserverd. Use another name.", ErrorCodes::OPT_FAIL);
            }
            
            if(in_array($option['longOption'], $checks) === true) {
                throw new \RuntimeException("Long option {$option['longOption']} reserverd. Use another name.", ErrorCodes::OPT_FAIL);
            }
            
            if(empty($option['shortOption'])) {
                throw new \RuntimeException("Option key ['shortOption'] not set or empty.", ErrorCodes::OPT_FAIL);
            }
            
            if(empty($option['longOption'])) {
                throw new \RuntimeException("Option key ['longOption'] not set or empty.", ErrorCodes::OPT_FAIL);
            }
            
            if(isset($option['hasValue']) === false) {
                throw new \RuntimeException("Option key ['hasValue'] not set.", ErrorCodes::OPT_FAIL);
            }
            
            if(isset($option['requiredValue']) === false) {
                throw new \RuntimeException("Option key ['requiredValue'] not set or empty.", ErrorCodes::OPT_FAIL);
            }
            
            if(empty($option['callback'])) {
                throw new \RuntimeException("Option key ['callback'] not set or empty.", ErrorCodes::OPT_FAIL);
            } elseif(method_exists($this, $option['callback']) === false) {
                throw new \RuntimeException("Option method {$option['callback']}() doesn't exists", ErrorCodes::OPT_METH_ERR);
            }
            
            if(isset($option['description']) === false) {
                throw new \RuntimeException("['description'] key not set", ErrorCodes::OPT_FAIL);
            }
        }

        return $options;
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
     * Save output in to file
     * 
     * @param string $string
     * @return void
     * @throw RuntimeException
     */
    private function saveOutput($string): void
    {
        if($this->writeOutputFile !== false) {       
            $state = file_put_contents($this->writeOutputFile, $string, FILE_APPEND);
            
            if($state === false) {
                throw \RuntimeException("File {$this->writeOutputFile} is not writable", ErrorCodes::OPT_FILE_PER_ERR);
            }
        }
    }
    
    /**
     * Get short options
     * 
     * @return string
     */
    private function getShortOptions()
    {
        
        $short = '';
        $options = $this->getAllOptions();
        
        foreach($options as $option) {
            $short .= $option['shortOption'];
            
            if($option['hasValue'] === true && $option['requiredValue'] === true) {
                // required value
                $short .= ':';
            } elseif($option['hasValue'] === true) {
                // optional value
                $short .= '::';
            }
        }
        
        return $short;        
    }
    
    /**
     * Get long options
     * 
     * @return array
     */
    private function getLongOptions()
    {
        $longArr = [];
        $options = $this->getAllOptions();
        
        foreach($options as $option) {
            $long = $option['longOption'];
            if($option['hasValue'] === true && $option['requiredValue'] === true) {
                // required value
                $long .= ':';
            } elseif($option['hasValue'] === true) {
                // optional value
                $long .= ':';
            }
            $longArr[] = $long;
        }
        
        return $longArr;
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
     * Get program default options
     * 
     * @return array
     */
    private function getDefaultOptions(): array
    {
        
        $options[] = array('shortOption' => 'e',
                           'longOption' => 'email',
                           'hasValue' => true,
                           'requiredValue' => true,
                           'isMandatory' => false,
                           'callback' => 'setEmail', 
                           'description' => 'If you wish to get notification about locked process set this option.');
        
        $options[] = array('shortOption' => 'h',
                           'longOption' => 'help',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'isMandatory' => false,
                           'callback' => 'showHelpMsg', 
                           'description' => 'Display this page');
        
        $options[] = array('shortOption' => 'l',
                           'longOption' => 'lock',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'isMandatory' => false,
                           'callback' => 'lock', 
                           'description' => 'Lock process. Will not let you run another instance of this same program until current is finished.');
        
        $options[] = array('shortOption' => 's',
                           'longOption' => 'subject',
                           'hasValue' => true,
                           'requiredValue' => true,
                           'isMandatory' => false,
                           'callback' => 'setSubject', 
                           'description' => 'Set notification subject. Works only with -e|--email option.');
        
        $options[] = array('shortOption' => 't',
                           'longOption' => 'tmp-location',
                           'hasValue' => true,
                           'requiredValue' => true,
                           'isMandatory' => false,
                           'callback' => 'setTmpLocation', 
                           'description' => 'Set location for temporary files. Default: ' . $this->location);
        
        $options[] = array('shortOption' => 'v',
                           'longOption' => 'verbose',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'isMandatory' => false,
                           'callback' => 'verbose', 
                           'description' => 'Turn on verbose mode');

        $options[] = array('shortOption' => 'w',
                           'longOption' => 'write',
                           'hasValue' => true,
                           'requiredValue' => true,
                           'isMandatory' => false,
                           'callback' => 'writeOutput', 
                           'description' => "Write output in to file. Eg. --write-output=\"/path/to/file\"");
        
        return $options;
    }
}
