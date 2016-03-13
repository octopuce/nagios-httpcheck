<?php

define("APP_PATH", __DIR__);

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
$dbConfig = $config["db"];
