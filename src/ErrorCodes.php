<?php

/**
 * Error Codes
 *
 * @author Wojciech Brozyna <wojciech.brozyna@gmail.com>
 */

namespace phpconsole;

class ErrorCodes {
    
    /**
     * Arguments not given
     */
    const NO_ARGS = 9950;
   
    /**
     * Unknown option
     * please ensure that provided option is supported by your module
     * This is usually done by populate YourModuleClass::$internalArgs
     * 
     */
    const OPT_FAIL = 9951;
    
    /**
     * No module name provided
     * Module name is mandatory to execute the program
     */
    const MOD_ERR = 9952;
    
    /**
     * Module not found
     * Module class doesn't exists or namesapce is incorrect
     */
    const MOD_NOT_FOUND = 9953;
    
    /**
     * Option method doesn't exists
     * Option method need to be specified in YourModuleClass::$internalArgs
     * and need to exists in the same class (YourModuleClass)
     */
    const OPT_METH_ERR = 9954;
    
    /**
     * File not specyfied for -w or --write-output option
     */
    const OPT_WRITE_NO_FILE = 9955;
    
    /**
     * Fileis not accessible by script
     * Check permissions
     */
    const OPT_FILE_PER_ERR = 9956;
    
}
