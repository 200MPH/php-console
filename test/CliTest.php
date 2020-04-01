<?php

namespace test;

use mcommander\Cli;
use mcommander\CliCodes;

class TestCli extends \PHPUnit_Framework_TestCase {
    
    /**
     * Cli object
     * 
     * @var Cli
     */
    private $cli;
    
    public function setUp() {
        
        $this->cli = new Cli();
        
        $this->cli->testing = true;
        
    }
    
    /**
     * Call protected/private method of a class.
     *
     * @param object &$object    Instantiated object that we will run method on.
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method.
     *
     * @return mixed Method return.
     *
     * @link https://jtreminio.com/2013/03/unit-testing-tutorial-part-3-testing-protected-private-methods-coverage-reports-and-crap/
    */
    public function invokeMethod(&$object, $methodName, array $parameters = array())
    {
        
       $reflection = new \ReflectionClass(get_class($object));
       $method = $reflection->getMethod($methodName);
       $method->setAccessible(true);

       return $method->invokeArgs($object, $parameters);
       
    }

    /**
     * Testing test :)
     */
    public function testTest() 
    {
        
        $foo = true;
        $this->assertTrue($foo);
        
    }
    
    public function helpArgProvider()
    {
        
        return array(
          array('-h'),
          array('--help')
        );
        
    }
    
    /**
     * @dataProvider helpArgProvider
     * 
     * @param string $arg
     */
    public function testIsHelpNeeded_True($arg)
    {
        
        $this->cli->despatch(2, array('this/path/is/arg/as/wel', $arg));
        
        $status = $this->invokeMethod($this->cli, 'isHelpNeeded');

        $this->assertTrue($status);
        
    }
    
    /**
     * @dataProvider helpArgProvider
     * 
     * @param string $arg
     */
    public function testIsHelpNeededWhenHProvidedAndMoreOtherArgs_False($arg)
    {
        
        $this->setExpectedException('RuntimeException');
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', $arg, 'test\ModuleTest', 'else'));
        
    }
    
    public function testIsHelpNeededWhenOtherArgsProvided_False()
    {
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest', 'else'));
        
        $status = $this->invokeMethod($this->cli, 'isHelpNeeded');
        
        $this->assertFalse($status);
        
    }
    
    /**
     * Module name provided
     */
    public function testIsModuleProvided_True()
    {
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest'));
        
        $status = $this->invokeMethod($this->cli, 'isModuleProvided');
        
        $this->assertTrue($status);
        
    }
    
    /**
     * Module name is not provided
     */
    public function testIsModuleProvided_False()
    {
        
        $this->setExpectedException('RuntimeException', null, CliCodes::MOD_ERR);
        
        $this->cli->despatch(3, array());
        
    }
    
    /**
     * Assume that user want to execute it module without extra options
     * Just simple execute it
     */
    public function testLoadInternalOption_Empty()
    {
        
        $status = $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest'));
        
        $this->assertNull($status);
        
    }
    
    public function optionsProvider()
    {
        
        return array(
          array('-t'),
          array('--test')
        );
        
    }
    
    /**
     * @dataProvider optionsProvider
     * Check that options is correctly loaded
     * 
     * @param string $option
     */
    public function testLoadInternalOption_IsLoadedOk($option)
    {
        
        // look in to ModuleTest::testMe()
        $this->setExpectedException('RuntimeException', null, CliCodes::OPT_FAIL);
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest', $option));
        
    }
    
    public function testLoadInternalOption_IsArgumentValid()
    {
        
        $this->setExpectedException('RuntimeException', null, CliCodes::OPT_FAIL);
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest', '-o'));
        
    }
    
    public function testLoadInternalOption_ArgumentMethodNotExists()
    {
        
        $this->setExpectedException('RuntimeException', null, CliCodes::OPT_METH_ERR);
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest', '-n'));
        
    }
    
    public function testIsFilePathProvidedForWriteOutputOption_True()
    {
        
        $status = $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest', '-w', '/tmp/abc.log'));
        
        unlink('/tmp/abc.log');
        
        $this->assertNull($status);
        
    }
    
    public function testIsFilePathProvidedForWriteOutputOption_False()
    {
        
        $this->setExpectedException('RuntimeException', null, CliCodes::OPT_WRITE_NO_FILE);
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest', '--write-output'));
        
    }
    
    public function testIsFileWritableForOption_False() 
    {
        
        $this->setExpectedException('RuntimeException', null, CliCodes::OPT_FILE_PER_ERR);
        
        $this->cli->despatch(3, array('this/path/is/arg/as/wel', 'test\ModuleTest', '-w', '/no/exists/abc.log'));
        
    }
    
}