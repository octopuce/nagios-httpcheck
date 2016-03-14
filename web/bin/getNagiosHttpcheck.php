#!/usr/bin/php
<?php
/**
 * This script returns all the HTTP Checks (or only those triggering an ALERT if argv[1]=="ALERT")
 * 
 * @author alban
 * @since 2015-06-30
 *  
 */


// It should bootstrap the app
define("APP_PATH", realpath(__DIR__ . "/../"));
require_once APP_PATH . "/bootstrap.php";

// It should display help / usage
if (isset($argv[1]) && in_array($argv[1], array("-h", "--help", "--usage"))) {
    die(""
    . "\nReturns all the HTTP Checks from the Nagios Httpcheck interface.\n\n"
    . "Usage\t\t".basename($argv[0])." [alert]\n"
    . "Params\talert\tOnly return alert checks (optional)\n"
    );
}

// It should search for an alert flag
if (isset($argv[1]) && $argv[1] == "alert") {
    $alert = true;
} else {
    $alert = false;
}

try {
    $dataAccessor = new Octopuce\Nagios\Httpcheck\DefaultDataAccessor($db);

    $service = new Octopuce\Nagios\Httpcheck\Service($dataAccessor);

    if ($service->isUpdateRequired()) {
        $service->updateAllServices();
    }
    $all_checks = $service->getAllForNagios($alert);
    echo ($all_checks);
} catch (Exception $e) {
    echo 'Exception: ' . $e->getMessage();
    exit(2);
}
