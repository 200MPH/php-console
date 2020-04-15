<?php

namespace test;

use phpconsole\Cli;
use phpconsole\ErrorCodes;

class ModuleTest extends Cli {
      
    /**
     * @var string
     */
    public $name = 'UnitTest';
    
    /**
     * @var string
     */
    public $description = 'Unit test description';
    
    public function run(): void {
        sleep(2);
    }
    
    protected function testMe() 
    {
        //throw any exception to proof that this functionality works
        throw new \RuntimeException('Options works', ErrorCodes::OPT_FAIL);   
    }

    protected function quit()
    {
        exit();
    }
    
    public function getOptions(): array
    {
    
        $options = [];
        $options[] = array('shortOption' => 'x',
                           'longOption' => 'testing',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'isMandatory' => false,
                           'callback' => 'testMe', 
                           'description' => 'Test option');
        
        $options[] = array('shortOption' => 'q',
                           'longOption' => 'exit',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'isMandatory' => false,
                           'callback' => 'quit', 
                           'description' => 'Exit process and leave lock file on storage');
        
        return $options;
        
    }

}
