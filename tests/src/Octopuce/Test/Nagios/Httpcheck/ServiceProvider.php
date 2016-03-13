<?php

namespace Octopuce\Test\Nagios\Httpcheck;

class ServiceProvider {

    protected $ts_6 = "1451606400";  // 2016-01-01 06:00
    protected $ts_12 = "1451649600"; // 2016-01-01 12:00
    protected $ts_18 = "1451671200"; // 2016-01-01 18:00

    public function getAllForNagiosWorks() {

        return array(
            #0
            array(
                array(
                    "age" => $this->ts_6,
                    "content" => $this->ts_6
                ), // $statusFileInfo
                " \ndefine service {\n    check_command                  check_http_status!127.0.0.1!www.example.com!80!/!200\n    host_name                      www.example.com\n    service_description            Check http status\n    use                            generic-service\n}\n        \n",
                // $expected
                "It should return valid nagios content with new check" // message
            ),
            #1
            array(
                array(
                    "age" => $this->ts_18,
                    "content" => $this->ts_18
                ), // $statusFileInfo
                " \ndefine service {\n    check_command                  check_http_status!127.0.0.1!www.example.com!80!/!200\n    host_name                      www.example.com\n    service_description            Check http status\n    use                            generic-service\n}\n        \n",
                // $expected
                "It should return valid nagios content with old check" // message
            ),
        );
    }

    public function saveHttpcheck() {
        return array(
            #0
            array(
                array(
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "ip" => "127.0.0.1",
                    "uri" => "/",
                    "ssl" => false,
                    "port" => 80,
                    "status" => 200,
                    "login" => "",
                    "pass" => "",
                    "regexp" => "",
                    "invert_regexp" => 0
                ), // data
                "It should save valid httpcheck" // message
            ),
        );
    }

    public function getNagiosServiceContent() {
        return array(
            #0
            array(
                array(
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "ip" => "127.0.0.1",
                    "uri" => "/",
                    "ssl" => false,
                    "port" => 80,
                    "status" => 200,
                    "login" => "",
                    "pass" => "",
                    "regexp" => "",
                    "invert_regexp" => 0
                ), // data
                "
define service {
    check_command                  check_http_status!127.0.0.1!somedomain.com!80!/!200
    host_name                      www.example.com
    service_description            Check http status
    use                            generic-service
}
", // expected
                "It should retrieve content from a valid httpcheck" // message
            ),
        );
    }

    public function getServiceData() {
        return array(
            #0
            array(
                array(
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "ip" => "127.0.0.1",
                    "uri" => "/",
                    "ssl" => false,
                    "port" => 80,
                    "status" => "200", 
                    "login" => "",
                    "pass" => "",
                    "regexp" => "",
                    "invert_regexp" => 0
                ), // data
                array(
                    "name" => "check_http_status",
                    "description" => "Check http status",
                    "params" => array(
                        1 => "127.0.0.1",
                        2 => "somedomain.com",
                        3 => "80",
                        4 => "/",
                        5 => "200", 
                    )
                ), // expected
                "It should retrieve service data from a valid httpcheck" // message
            ),
        );
    }

    public function isUpdateRequired() {
        return array(
            #0 
            array(
                array(
                    "age" => $this->ts_18,
                    "content" => $this->ts_18
                ), // $statusFileInfo
                false, // $expected
                "It should not require update with old service content and old update file info" // message
            ),
            #1
            array(
                array(
                    "age" => $this->ts_12,
                    "content" => $this->ts_6
                ), // $statusFileInfo
                true, // $expected
                "It should require update with recent service content and old update file info" // message
            ),
            #2
            array(
                array(
                    "age" => $this->ts_6,
                    "content" => $this->ts_6
                ), // $statusFileInfo
                true, // $expected
                "It should require update with old service content and recent update file info" // message
            ),
            #1
            array(
                array(
                    "age" => $this->ts_12,
                    "content" => $this->ts_6
                ), // $statusFileInfo
                true, // $expected
                "It should require update with recent service content and recent update file info" // message
            ),
        );
    }

    public function getLastUpdateTimestamp() {
        return array(
            #0 
            array(
                array(
                    "age" => $this->ts_18,
                    "content" => $this->ts_18
                ), // $statusFileInfo
                $this->ts_18, // $expected
                "It should find the correct timestamp" // message 
            ),
        );
    }

    public function getLastUpdateMtime() {
        return array(
            #0 
            array(
                array(
                    "age" => $this->ts_18,
                    "content" => $this->ts_18
                ), // $statusFileInfo
                $this->ts_18, // $expected
                "It should find the correct age of file" // message 
            ),
        );
    }

    public function setNewUpdateTimestamp() {
        return array(
            #0 
            array(
                array(
                    "age" => $this->ts_18,
                    "content" => $this->ts_18
                ), // $statusFileInfo
                $this->ts_18, // $expected
                "It should set the correct timestamp" // message 
            ),
        );
    }

    public function setNewUpdateMTime() {
        return array(
            #0 
            array(
                array(
                    "age" => $this->ts_18,
                    "content" => $this->ts_18
                ), // $statusFileInfo
                $this->ts_18, // $expected
                "It should set the correct age of file" // message 
            ),
        );
    }

    public function saveService() {
        return array(
            #0 
            array(
                array(
                    "id" => 1,
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "ip" => "127.0.0.1",
                    "uri" => "/",
                    "ssl" => false,
                    "port" => 80,
                    "status" => "200", 
                    "login" => "",
                    "pass" => "",
                    "regexp" => "",
                    "invert_regexp" => 0
                ), // httpcheck
                true, // $expected
                "It should save a new service" // message 
            ),
        );
    }

    public function handleRequest() {
        return array(
            #0 
            array(
                array(
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "ip" => "127.0.0.1",
                    "uri" => "/",
                    "no_alert" => 1,
                ), // request
                array(
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "invert_regexp" => 0,
                    "ip" => "127.0.0.1",
                    "login" => "",
                    "no_alert" => 1,
                    "pass" => "",
                    "port" => 80,
                    "regexp" => "",
                    "ssl" => 0,
                    "status" => "200",
                    "uri" => "/",
                ), // $expected
                "It should convert partial data" // message 
            ),
            #1 
            array(
                array(
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "ip" => "127.0.0.1",
                    "no_alert" => 1,
                    "uri" => "/",
                    "ssl" => 0,
                    "port" => 80,
                    "status" => "200", 
                    "login" => "",
                    "pass" => "",
                    "regexp" => "",
                    "invert_regexp" => 0
                ), // request
                array(
                    "fqdn" => "www.example.com",
                    "host" => "somedomain.com",
                    "invert_regexp" => 0,
                    "ip" => "127.0.0.1",
                    "login" => "",
                    "no_alert" => 1,
                    "pass" => "",
                    "port" => 80,
                    "regexp" => "",
                    "ssl" => 0,
                    "status" => "200", 
                    "uri" => "/",
                ),  // $expected
                "It should work with all fields provided" // message 
            ),
        );
    }

}
