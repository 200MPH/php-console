<?php

/**
 * As this tool is not just for display string on console.
 * So let's have a look in to more "advanced" example
 *
 * @author chevy
 */

namespace examples\mcommander\DataSync;

use mcommander\AbstractCliModule;

class DataSync extends AbstractCliModule {
    
    /**
     * @var Synchronizer
     */
    private $synchronizer;
    
    public function execute() 
    {
    
        $this->synchronizer = new Synchronizer();
        
        $this->synchronize();
        
    }
    
    /**
     * Synchronize data
     * 
     * @return void
     */
    private function synchronize()
    {
        
        $comapreWithMe = $this->synchronizer->getDataToCompare();
        
        $this->output('Syncing IDs ...' . PHP_EOL);
        
        foreach($this->synchronizer->getData() as $id) {
            
            // just to see output in slow motion :)
            sleep(1);
            
            $this->output('Synchronize ID ' . $id . '...');
            
            if(in_array($id, $comapreWithMe) === true) {
                
                $this->successOutput('OK' . PHP_EOL);
                
            } else {
                
                $this->errorOutput('FAIL' . PHP_EOL);
                
            }
            
        }
        
    }
    
}
