<?php

/**
 *  
 * This class takes module name from command line argument and try to execute it if module class found.
 * Each module class need extends AbstractCliModule() and implement execute() method
 * 
 * Each module might have different options so typing 
 * php cli.php module1 -h might give you different output
 * than php cli.php module2 -h
 *
 * Typing -h without module name will give you this message.
 * 
 * Module name is case sensitive so myModule is not the same as MyModule
 * 
 * See in to ../examples folder so you will find some interesting solutions
 * 
 * @author Wojciech Brozyna <wojciech.brozyna@gmail.com>
 * @license https://github.com/200MPH/m-commander/blob/master/LICENSE
 * @link https://github.com/200MPH/m-commander/
 */

namespace phpconsole;

class CommandDespatcher {
    
    /**
     * Arguments
     * 
     * @var array
     */
    private $arguments;
    
    /**
     * @var Cli
     */
    private $cli;
    
    /**
     * Render exception
     * 
     * @param Exception|RuntimeException
     * @param bool $trace [optional] Show trace
     */
    static public function renderException($ex, $trace = true)
    {
        Cli::render("Runtime Error!", CliColors::FG_WHITE, CliColors::BG_RED, true);
        Cli::render("Exception code: {$ex->getCode()}", CliColors::FG_WHITE, CliColors::BG_RED, true);
        Cli::render("Exception message: {$ex->getMessage()}", CliColors::FG_WHITE, CliColors::BG_RED, true);
        
        if($trace === true) {
            Cli::render("Trace: {$ex->getTraceAsString()}", CliColors::FG_WHITE, CliColors::BG_RED, true);   
        }
    }
    
    /**
     * Despatch request to appropriate module class. 
     * 
     * @param array $args
     * @return void
     */
    public function despatch($args): void
    {
        
        $this->arguments = $args;
        if($this->isHelpNeeded() === true) {
            $this->displayHelp();
        } else {
            $this->loadObject();
        }
        
    }
    
    /**
     * Display help message
     * 
     * @return void
     */
    public function displayHelp()
    {
        $this->renderAuthor(true);
        Cli::render("Please provide class name that you wish to process with full namespace.", 
                    CliColors::FG_BLACK, CliColors::BG_YELLOW, true);
        Cli::render("Example: ./vendor/bin/php-console --help 'namespace\\to\\my\\program'", 
                    CliColors::FG_BLACK, CliColors::BG_YELLOW, true);
    }
    
    /**
     * Get author
     * 
     * @param bool $force [optional] Force render
     * @return string
     */
    private function renderAuthor(bool $force = false)
    {
        $show = false;
        $verbose = getopt('vh', array('verbose', 'help'));
        
        if($force === true 
           || isset($verbose['v']) 
           || isset($verbose['verbose']) 
           || isset($verbose['h']) 
           || isset($verbose['help'])) {
            
            $show = true;
        }
        
        if($show === true) {
            $version = file_get_contents(__DIR__ . '/../version.txt');
            $str = "PHP-Console {$version}" . PHP_EOL;
            $str.= "Author: Wojciech Brozyna <https://github.com/200MPH>" . PHP_EOL . PHP_EOL;
            Cli::render($str, CliColors::FG_LIGHT_BLUE);   
        }
    }
    
    /**
     * Check if help message need to be displays
     * 
     * @return bool
     */
    private function isHelpNeeded()
    {
        // last argument is a program name to execute
        $class = end($this->arguments);
        return !class_exists($class);
    }
    
    /**
     * Load object and execute
     * 
     * @return void
     */
    private function loadObject()
    {
        $class = end($this->arguments);
        
        if(class_exists($class) === true) {
            $this->cli = new $class();
            $this->cli->setArguments($this->arguments);
            $this->execute();
        } else {
            throw new \RuntimeException("Class '{$class}' not found", ErrorCodes::MOD_NOT_FOUND);
        }
    }
    
    /**
     * Execute program
     *
     * @return void
     */
    private function execute()
    {
        
        $this->renderAuthor();
        $process = $this->cli->findLockedProcess();
        if($process === false) {
            // the method call sequence is important here !
            $this->cli->setOptions();
            $this->cli->writeProcessFile();
            $this->cli->run();
            $this->cli->deleteProcessFile();
        } else {
            $this->cli->outputWarning("Process {$process['PID']} locked at {$process['DATE']}" . PHP_EOL);
            $this->cli->sendLockNotification();
            $this->cli->showHelpMsg();
        }
        
    }
    
}
