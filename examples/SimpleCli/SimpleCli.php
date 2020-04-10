<?php

/**
 * This example show the simplest usage of the library
 *
 * @author Wojciech Brozyna <wojciech.brozyna@gmail.com>
 */

namespace examples\phpconsole\SimpleCli;

use phpconsole\Cli;

class SimpleCli extends Cli {
    
    /**
     * This is mandatory function which is required by abstraction
     * @return void
     */
    public function run(): void
    {
        // PHP_EOL does the same thing as "\n"
        
        $this->output("Hello World!" . PHP_EOL);
        $this->outputSuccess('Hurra! This is my first CLI module' . PHP_EOL);
        $this->outputWarning('Warning have yellow colour' . PHP_EOL);
        $this->outputError('Error have red colour'  .PHP_EOL);
        Cli::render('Bold text', null, null, true, true);
        Cli::render('More colors', \phpconsole\CliColors::FG_LIGHT_GREEN, \phpconsole\CliColors::BG_BLUE, true);
        Cli::render('More colors', \phpconsole\CliColors::FG_RED, \phpconsole\CliColors::BG_WHITE, true);
    }

    /**
     * We do not have any options in this program so we need to return empty array
     * @return array
     */
    public function getOptions(): array
    {
        return [];
    }

}
