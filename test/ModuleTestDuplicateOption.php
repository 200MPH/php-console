<?php

namespace test;

class ModuleTestDuplicateOption extends ModuleTest {
        
    public function getOptions(): array
    {
    
        $options = [];
        $options[] = array('shortOption' => 'e',
                           'longOption' => 'email',
                           'hasValue' => false,
                           'requiredValue' => false,
                           'isMandatory' => false,
                           'callback' => 'false', 
                           'description' => 'Test callback is false');
        
        return $options;
        
    }

}
