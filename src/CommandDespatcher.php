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
        Cli::render("Please provide class name that you wish to process with full namespace.", 
                    CliColors::FG_BLACK, CliColors::BG_YELLOW, true);
        Cli::render("Example: ./vendor/bin/cli 'namespace\\to\\my\\program' -h", 
                    CliColors::FG_BLACK, CliColors::BG_YELLOW, true);
    }
    
    /**
     * Check if help message need to be displays
     * 
     * @return bool
     */
    private function isHelpNeeded()
    {
        if(isset($this->arguments[1]) === false) {
            return true;
        } else {
            if($this->arguments[1] == '-h' || $this->arguments[1] == '--help') {
                return true;
            }
        }
        
        return false;   
    }
    
    /**
     * Load object and execute
     * 
     * @return void
     */
    private function loadObject()
    {
        $module = $this->arguments[1];
        
        if(class_exists($module) === true) {
            $this->cli = new $module();
            $this->execute();
        } else {
            throw new \RuntimeException("Class '{$module}' not found", ErrorCodes::MOD_NOT_FOUND);
        }
    }
    
    /**
     * Execute program
     *
     * @return void
     */
    private function execute()
    {
        
        $lock = $this->cli->isLocked();
        if($lock === false) {
            $this->abstractModule->setupOptions();
            $this->abstractModule->writeLockInfo();
            $this->abstractModule->execute();
            $this->abstractModule->unlock();
        } else {
            CliColors::render("Process {$lock['PID']} locked at {$lock['DATE']}", CliColors::FG_WHITE, CliColors::BG_RED, true, true);
            $this->abstractModule->helpMsg();
        }
        
    }
    
}
