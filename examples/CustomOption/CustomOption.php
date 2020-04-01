<?php

/**
 * This example show how to add customized option (CLI argument)
 *
 * @author Wojciech Brozyna <wojciech.brozyna@gmail.com>
 */

namespace examples\mcommander\CustomOption;

use mcommander\AbstractCliModule;

class CustomOption extends AbstractCliModule {
    
    /**
     * Test variable
     * 
     * @var sting
     */
    private $testOption;
    
    public function execute() {
        
        $this->output('Execute this module with and without -s|--set-option-value parameter' . PHP_EOL);
        
        $this->successOutput($this->testOption);
        
    }
    
    /**
     * Add new customize option
     * We have to overload parent method AbstractCliModule::loadOptions()
     */
    protected function loadOptions()
    {
        
        // we still want to display default ones, right?
        parent::loadOptions();
        
        $this->defaultOptions[] = array('options' => array('-s', '--set-option-value'), 
                                        'callback' => 'setOption', 
                                        'description' => 'Testing my new option description');
        
    }
    
    /**
     * New Option method which is defined in above array as a callback
     */
    protected function setOption()
    {
        
        $this->testOption = 'I used my option' . PHP_EOL;
        
    }
    
}
