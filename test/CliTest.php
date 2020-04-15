<?php

namespace test;

use phpconsole\CommandDespatcher;
use PHPUnit\Framework\TestCase;

class CliTest extends TestCase {
    
    /**
     * CLI
     * 
     * @var CommandDespatcher
     */
    private $cli;
    
    public function setUp(): void {
        $this->cli = new CommandDespatcher();
    }
    
    public function tearDown(): void
    {
        $files = glob('UnitTest.*.*.lock');
        
        if(is_array($files)) {
            foreach($files as $file) {
                unlink($file);
            }
        }
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
    public function testIsHelpNeeded($arg)
    {
        $this->cli->despatch(array($arg, ''));   
        $state1 = $this->invokeMethod($this->cli, 'isHelpNeeded');
        $this->assertTrue($state1);
        
        $this->cli->despatch(array($arg, 'test\ModuleTest'));   
        $state2 = $this->invokeMethod($this->cli, 'isHelpNeeded');
        $this->assertTrue($state2);
        
        $this->cli->despatch(array('test\ModuleTest'));   
        $state3 = $this->invokeMethod($this->cli, 'isHelpNeeded');
        $this->assertFalse($state3);
        
    }
    
    public function testLoadOptionEmpty()
    {
        $this->expectException('RuntimeException');
        $this->cli->despatch(array('test\ModuleTestNoOptions'));
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
    public function testLoadInternalOption($option)
    {
        // look in to ModuleTest::testMe()
        $this->expectException('RuntimeException');
        $this->cli->despatch(array($option, 'test\ModuleTest'));
    }
    
    public function testProcessFile()
    {
        $this->cli->despatch(array('--lock', '--exit', 'test\ModuleTest'));
        $mtest = new ModuleTest();
        $files = $mtest->getProcessFiles();
        $this->assertIsArray($files);
        $file = end($files);
        $data = $mtest->parseProcessFile($file);
        $this->assertTrue(isset($data['PID']));
        $this->assertIsInt($data['PID']);
        $this->assertTrue(isset($data['DATE']));
        $this->assertTrue(isset($data['NAME']));
        $this->assertTrue(isset($data['DESC']));
        $this->assertTrue(isset($data['FILE']));
        $this->assertTrue(isset($data['AVG_OP_TIME']));
        $this->assertTrue(isset($data['LOCK']));
        $this->assertIsBool($data['LOCK']);
        $this->assertTrue($data['LOCK']);
    }
    
    public function testFindLockedProcess()
    {
        $this->cli->despatch(array('--lock', '--exit', 'test\ModuleTest'));
        $mtest = new ModuleTest();
        $this->assertIsArray($mtest->findLockedProcess());
    }
    
    public function testDeleteProcessFile()
    {
        $this->cli->despatch(array('--lock', 'test\ModuleTest'));
        $mtest = new ModuleTest();
        $this->assertFalse($mtest->findLockedProcess());
    }
    
    public function testLoadInternalOptionDuplicate()
    {
        $this->expectException('RuntimeException');        
        $this->cli->despatch(array('test\ModuleTestDuplicateOption'));   
    }
    
}