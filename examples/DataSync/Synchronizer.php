<?php

/**
 * This is example object which would be impemented in DataSync()
 * In reality this object might completely in different location in your project
 */

namespace examples\mcommander\DataSync;

class Synchronizer {
    
    /**
     * Get fake data
     * 
     * @return array
     */
    public function getData()
    {
        
        $array = array('123', '456', '798', '111');
        
        return $array;
        
    }
    
    /**
     * Get fake data to compare
     * 
     * @return array
     */
    public function getDataToCompare()
    {
        
        $array = array('123', '456', '999', '111');
        
        return $array;
        
    }
    
}
