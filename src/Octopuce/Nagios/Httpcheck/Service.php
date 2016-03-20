<?php

namespace Octopuce\Nagios\Httpcheck;

use Octopuce\Nagios\Httpcheck\DataAccessorInterface as DataAccessorInterface;

/**
 *
 * @author alban
 */
class Service {

    /**
     * @var \Octopuce\Nagios\Httpcheck\DataAccessorInterface;
     */
    var $dataAccessor;

    /**
     * @var string
     */
    var $update_file_location = "/tmp/nagios-httpcheck.update";

    /**
     * @param array $options
     */
    function __construct(\Octopuce\Nagios\Httpcheck\DataAccessorInterface $dataAccessor, $options = array()) {

        $this->dataAccessor = $dataAccessor;

        // Attempts to retrieve update_file_location
        if (isset($options["update_file_location"])) {
            $this->update_file_location = $options["update_file_location"];
        }

        $update_file_dirname = dirname($this->update_file_location);
        if (!is_dir($update_file_dirname)) {
            if (!mkdir($update_file_dirname, 0770, true)) {
                throw new Exception("Failed to create directory " . $update_file_dirname);
            }
        }

        // Checks file exists
        if (!is_file($this->update_file_location)) {
            if (!touch($this->update_file_location)
            ) {
                throw new Exception("Failed to initialize the file " . $this->update_file_location);
            }
            if (!file_put_contents($this->update_file_location, "1")) {
                throw new Exception("Failed to set content of file " . $this->update_file_location);
            }
        }
        // Checks file is readable
        if (!is_readable($this->update_file_location)) {
            throw new Exception("Failed to read the file " . $this->update_file_location);
        }
        // Checks file is writable
        if (!is_writable($this->update_file_location)) {
            throw new Exception("Failed to write the file " . $this->update_file_location);
        }
    }

    /**
     * Proxy to the DataAccessor findCheck method
     * 
     * @param type $id
     */
    public function findHttpcheck($id) {

        return $this->dataAccessor->findHttpcheck($id);
    }

    /*     * elete/1
     * 
     * @param type $id
     * @return boolean
     */

    public function deleteCheck($id) {

        // It should check if the check exists
        $found = $this->dataAccessor->findHttpcheck($id);
        if (!$found) {
            return false;
        }

        // It should notify to the state files
        $this->setNewUpdateMtime();

        // It should delete the check in database
        $result = $this->dataAccessor->deleteHttpcheck($id);

        return $result;
    }

    /**
     * 
     * @param array $options
     * @return array
     */
    public function getHttpcheckList($options = array()) {

        return $this->dataAccessor->getHttpcheckList($options = array());
    }

    /**
     * Encapsulates the data Provider save method
     * @param array $request
     * @return boolean
     */
    public function saveHttpcheck($request) {

        // It should prepare the data to record
        $data = $this->handleRequest($request);

        // It should save the data
        return $this->dataAccessor->saveHttpcheck($data);
    }

    /**
     * Returns the nagios syntax string for a checkhttp command line
     * 
     * @param array $check
     */
    function getNagiosServiceContent($check) {

        $commandData = $this->getServiceData($check);
        $fqdn = $check["fqdn"];
        $name = $commandData["name"];
        $params = implode("!", $commandData["params"]);
        $description = $commandData["description"];
        $template = " 
define service {
    check_command                  ${name}!${params}
    host_name                      ${fqdn}
    service_description            ${description}
    use                            generic-service
}
        ";

        return $template;
    }

    /**
     * This method reads the check DB record and converts is to nagios readable data
     * 
      'check_https_status_regexp_invertregexp_auth':
      command_line => '$USER1$/check_http --ssl -I $ARG1$ -H $ARG2$ -p $ARG3$ -u $ARG4$ -e $ARG5$ -R $ARG6$% --invert-regex -a $ARG7$';
      define service {
      check_command                  ${command_line}${params}
      host_name                      ${fqdn}
      service_description            ${description}
      use                            generic-service
      }
     * @param type $check
     * @return type
     */
    public function getServiceData($check) {

        $result = array(
            "name" => "",
            "description" => "",
            "params" => array(),
        );

        $command_name = "check_";

        // Mandatory
        // IP
        $result["params"][1] = $check["ip"];

        // HOST
        $result["params"][] .= $check["host"];

        // SSL
        $command_name .= $check["ssl"] ? "https" : "http";

        // URI
        $result["params"][] .= $check["port"];

        // URI
        $result["params"][] .= $check["uri"];

        // Optional
        $status = $check["status"];
        $regexp = $check["regexp"];
        $invert_regexp = $check["invert_regexp"];
        $login = $check["login"];
        $pass = $check["pass"];

        if ($status) {
            $command_name .= "_status";
            $result["params"][] = $status;
        }
        if ($regexp) {
            $command_name .= "_regexp";
            $result["params"][] = $regexp;
            if ($invert_regexp) {
                $command_name .= "_invertregexp";
            }
        }
        if ($login || $pass) {
            $command_name .= "_auth";
            $result["params"][] = "${login}:${pass}";
        }
        $result["name"] = $command_name;
        $result["description"] = ucwords(str_replace("_", " ", $command_name))." ".$check["host"]."@".$check["ip"]." #".$check["id"];

        return $result;
    }

    /**
     * Checks if updates are required
     */
    function isUpdateRequired() {

        // Retrieves the last update
        $last_update_ts = $this->getLastUpdateTimestamp();

        // Retrieves the last mtime
        $last_update_mtime = $this->getLastUpdateMtime();

        // If the file was touched since last update, launch update
        if ($last_update_mtime > $last_update_ts) {
            return true;
        }
        // Checks if new records exist
        $result = $this->dataAccessor->getIsUpdateRequired($last_update_ts);
        return $result;
    }

    /**
     * Retrieves the last update timestamp
     */
    function getLastUpdateTimestamp() {

        if (!file_exists($this->update_file_location)) {
            $last_update_ts = 0;
        } else {
            $last_update_ts = file_get_contents($this->update_file_location);
        }
        return $last_update_ts;
    }

    /**
     * Retrieves the last update timestamp
     */
    function getLastUpdateMtime($time = null) {

        // It should set time to NOW by default
        if (!$time) {
            $time = time();
        }
        if (!file_exists($this->update_file_location)) {
            $last_update = time();
        } else {
            $last_update = filemtime($this->update_file_location);
        }
        return $last_update;
    }

    /**
     * Sets new update timestamp 
     * 
     * @param type $time
     * @return boolean
     */
    function setNewUpdateTimestamp($time = null) {

        // It should set time to NOW by default
        if (!$time) {
            $time = time();
        }

        file_put_contents($this->update_file_location, $time);

        return true;
    }

    /**
     * Sets new update mtime on file
     * 
     * @return boolean
     */
    function setNewUpdateMtime($time = null) {

        // It should set time to NOW by default
        if (!$time) {
            $time = time();
        }
        if (!is_writable($this->update_file_location)) {
            throw new \Exception("Cannot write into " . $this->update_file_location);
        }
        if (!touch($this->update_file_location, $time, $time)) {
            throw new \Exception("Cannot touch file " . $this->update_file_location);
            ;
        }

        return true;
    }

    /**
     * 
     * @param array $check
     */
    function saveService($check) {

        $command_line = $this->getNagiosServiceContent($check);
        $result = $this->dataAccessor->saveService($command_line, $check["id"]);
        return $result;
    }

    /**
     * Updates all the command lines in database if needed
     */
    function updateAllServices() {

        // Retrieves the last update
        $last_update_ts = $this->getLastUpdateTimestamp();

        // Checks if new records exist
        $checkList = $this->dataAccessor->getHttpcheckServiceList(array(
            "where" => array(
                "updated_at > FROM_UNIXTIME(?) OR command_line IS NULL",
            )
        ));

        foreach ($checkList as $check) {
            $this->saveService($check);
        }

        // Records update
        if (!$this->setNewUpdateTimestamp()) {
            throw new \Exception("Failed to update timestamp.");
        }

        return true;
    }

    /**
     * 
     * @param boolean $only_alerts
     * @return string
     */
    function getAllForNagios($only_alerts = false) {

        // It should check if update is required
        if ($this->isUpdateRequired()) {
            $this->updateAllServices();
        }

        // It should retrieve all checks
        $allChecks = $this->dataAccessor->getAllServiceContent($only_alerts);
        return $allChecks;
    }


    /**
     * checks and fills data from request
     */
    function handleRequest($request) {

        // Attempts to retrieve fqdn
        if (isset($request["fqdn"]) && ($request["fqdn"])) {
            $fqdn = $request["fqdn"];
        } else {
            return array(_("Could not find the FQDN"));
        }

        // Attempts to retrieve ip
        if (isset($request["ip"]) && $request["ip"]) {
            $ip = $request["ip"];
        } else {
            $ip = gethostbyname($fqdn);
        }

        // Attempts to retrieve host
        if (isset($request["host"]) && ($request["host"])) {
            $host = $request["host"];
        } else {
            $host = $fqdn;
        }

        // Attempts to retrieve uri
        if (isset($request["uri"]) && ($request["uri"])) {
            $uri = $request["uri"];
        } else {
            $uri = "/";
        }

        // Attempts to retrieve ssl
        if (isset($request["ssl"]) && ($request["ssl"])) {
            $ssl = ( $request["ssl"] == 1 || $request["ssl"] == "on" ) ? 1 : 0;
        } else {
            $ssl = 0;
        }

        // Attempts to retrieve port
        if (isset($request["port"]) && ($request["port"])) {
            $port = intval($request["port"]);
        } else if ($ssl) {
            $port = 443;
        } else {
            $port = 80;
        }

        // Attempts to retrieve status
        if (isset($request["status"]) && ($request["status"])) {
            $status = intval($request["status"]);
        } else {
            $status = 200;
        }

        // Attempts to retrieve login
        if (isset($request["login"]) && ($request["login"])) {
            $login = $request["login"];
        } else {
            $login = "";
        }

        // Attempts to retrieve pass
        if (isset($request["pass"]) && ($request["pass"])) {
            $pass = $request["pass"];
        } else {
            $pass = "";
        }

        // Attempts to retrieve regexp
        if (isset($request["regexp"]) && $request["regexp"]) {
            $regexp = $request["regexp"];
        } else {
            $regexp = "";
        }

        // Attempts to retrieve invert_regexp
        if (isset($request["invert_regexp"]) && $request["invert_regexp"]) {
            $invert_regexp = ( $request["invert_regexp"] == 1 || $request["invert_regexp"] == "on" ) ? 1 : 0;
        } else {
            $invert_regexp = 0;
        }

        // Attempts to retrieve no_alert
        if (isset($request["no_alert"]) && ($request["no_alert"])) {
            $no_alert = ( $request["no_alert"] == 1 || $request["no_alert"] == "on" ) ? 1 : 0;
        } else {
            $no_alert = 0;
        }

        $data = array(
            "fqdn" => $fqdn,
            "ip" => $ip,
            "host" => $host,
            "uri" => $uri,
            "port" => $port,
            "status" => $status,
            "ssl" => $ssl,
            "regexp" => $regexp,
            "invert_regexp" => $invert_regexp,
            "login" => $login,
            "pass" => $pass,
            "no_alert" => $no_alert
        );
        //
        if (array_key_exists("id", $request) && ($request["id"])) {
            $found = $this->dataAccessor->findHttpcheck($request["id"]);
            if (!$found) {
                return array(_("Can't find this http check!"));
            }
            $data["id"] = $request["id"];
        }

        return $data;
    }

}
