<?php

// It should load vendor autoload
define("VENDOR_AUTOLOAD_FILE", APP_PATH . "/vendor/autoload.php");
if (!is_file(VENDOR_AUTOLOAD_FILE)) {
    echo "Please run composer first ";
    exit(1);
}
require_once VENDOR_AUTOLOAD_FILE;

// It should load configuration file 
define("CONFIG_FILE", APP_PATH . "/" . "config.yml");
if (!is_file(CONFIG_FILE)) {
    echo "Please provide config file";
    exit(1);
}
$config = Symfony\Component\Yaml\Yaml::parse(file_get_contents(CONFIG_FILE));

// It should bootstrap the db
$dbParams = $config["db"];
$db = new \PDO("mysql:host=" . $dbParams["host"] . ";dbname=" . $dbParams["dbname"], $dbParams["user"], $dbParams["pass"]);

// It should convert errors to exceptions
function exceptions_error_handler($severity, $message, $filename, $lineno) {
    if( in_array($severity, array( E_NOTICE, E_USER_DEPRECATED) )){
        return;
    }
    throw new ErrorException($message, 0, $severity, $filename, $lineno);
}
set_error_handler('exceptions_error_handler');
