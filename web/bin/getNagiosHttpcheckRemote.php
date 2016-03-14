#! /usr/bin/php
<?php
/**
 * Retrieves nagios configuration services from remote host
 * @author alban
 * @since 2015-06-30
 */

// It should bootstrap the app
define("APP_PATH", realpath(__DIR__ . "/../"));
require_once APP_PATH . "/bootstrap.php";


// It should display help / usage
if (isset($argv[1]) && in_array($argv[1], array("-h", "--help", "--usage"))) {
    die(""
            . "\nReturns all the HTTP Checks from the Nagios Httpcheck interface.\n\n"
            . "Usage\t\t" . basename($argv[0]) . " [alert]\n"
            . "Params\talert\tOnly return alert checks (optional)\n"
    );
}

$LOGLEVEL = 3; // 1 pour minimal, 3 pour debug

try {
// It should define a map of source / destination
// This is purely an example
    $sourceDestList = array(
        array(
            "transport" => "http", // can be ssh or http
            "source" => "http://nagios-httpcheck.local/alert", // for http, an URL
            "target" => "/tmp/cron_http.cfg", // destination
        ),
//        array(
//            "transport" => "ssh", // can be ssh or http
//            "source" => "/usr/bin/php /usr/local/lib/nagios-httpcheck/web/bin/getNagiosHttpcheck.php", // for SSH the command line to execute on remote host
//            "ssh_command_line" => "/usr/bin/ssh -i /home/nagios/.ssh/id_rsa nagios@nagios", // for SSH a connection command line
//            "target" => "/tmp/cron_http.cfg", // Destination
//        ),
    );

// It should instantiate the Remote Config Manager
    $service = new Octopuce\Nagios\Httpcheck\RemoteConfigManager(array(
        "sourceDestList" => $sourceDestList,
        "log_file" => "/tmp/getNagiosHttpcheckRemote.log",
        "log_level" => $LOGLEVEL,
        "reload_nagios" => false
    ));

// It should run the sync manager
    $service->doSync();
} catch (\Exception $e) {

    echo( "A critical error occured\n");
    var_dump($e);
    exit(1);
}
