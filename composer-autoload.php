<?php

/**
 * If you use composer use this file for autoload
 * If non of the below location holds your autoload.php file
 * than please append below array
 */

$possibleAutoloadLocations[] = __DIR__ . '/../../autoload.php';
$possibleAutoloadLocations[] = __DIR__ . '/../vendor/autoload.php';
$possibleAutoloadLocations[] = __DIR__ . '/vendor/autoload.php';

$foundAutoloader = false;

foreach ($possibleAutoloadLocations as $file) {
    
    if(file_exists($file) === true) {
        
        require_once $file;
        
        $foundAutoloader = true;
        
        break;
        
    } 
}

if($foundAutoloader === false) {
    
    die('Composer autoloader not found.');
    
}