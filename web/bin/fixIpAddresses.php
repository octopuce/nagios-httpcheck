#!/usr/bin/php
<?php
/**
 * This script fixes all IP addresses for checks 
 * 
 * @author alban
 * @since 2015-06-30
 *  
 */


echo "ok";
// It should bootstrap the app
define("APP_PATH", realpath(__DIR__ . "/../"));
require_once APP_PATH . "/bootstrap.php";

// It should display help / usage
if (isset($argv[1]) && in_array($argv[1], array("-h", "--help", "--usage"))) {
    die(""
    . "\nFixes checks hosts ip addresses \n\n"
    . "Usage\t\t".basename($argv[0])." \n"
    );
}

ini_set("display_errors",1);
error_reporting(E_ALL);
try {
    $dataAccessor = new Octopuce\Nagios\Httpcheck\DefaultDataAccessor($db);
    $service = new Octopuce\Nagios\Httpcheck\Service($dataAccessor);

    $allChecksList = $service->getHttpcheckList();
    foreach( $allChecksList as $check){

	$check = $service->handleRequest( $check );
	
	$service->saveHttpcheck( $check );

    }
    $service->updateAllServices();
} catch (Exception $e) {
    echo 'Exception: ' . $e->getMessage();
    exit(2);
}

