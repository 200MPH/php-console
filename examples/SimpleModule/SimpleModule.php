<?php

/**
 * This example show the simplest usage of the library
 *
 * @author Wojciech Brozyna <wojciech.brozyna@gmail.com>
 */

namespace examples\phpconsole\SimpleModule;

use phpconsole\Cli;

class SimpleModule extends Cli {
    
    /**
     * This is mandatory function which is required by abstraction
     */
    public function execute()
    {
        // PHP_EOL does the same thing as "\n"
        
        $this->output("Hello World!" . PHP_EOL);
        $this->successOutput('Hurra! This is my first CLI module' . PHP_EOL);
        $this->warningOutput('Warning have yellow colour' . PHP_EOL);
        $this->errorOutput('Warning have red colour'  .PHP_EOL);
    }
    
}
