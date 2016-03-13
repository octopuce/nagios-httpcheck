# Nagios Httpcheck Service

## Synopsis

We needed some simple service to generate httpchecks for nagios that could be embedded in a PHP Panel.

This project exposes every parameter available from the standard nagios binary check:

- **HOST** Check must send the request for given HOST (i.e. web server domain name)
- **IP** Check must send the request to given IP address 
- **PORT** Check must use the given port 80,8000, etc.
- **SSL** Check must use the HTTPS protocol
- **LOGIN / PASS** Check must use the HTTP AUTH credentials provided
- **STATUS** Check must validate the response Status (i.e. is a 200, 301, 403, etc.)
- **REGEXP** Check must validate the response contains a specific Regulare Expression
- **INVERT_REGEXP** Check must validate the ABSENCE of the Regular Expression (i.e. this is a boolean flag)

There is an additional **NO_ALERT** parameter to provide a basic check filtering. In other words, you can decide which checks have High OR Low Criticity.  

## Installing for Nagios 

- Add the "nagios_command_http.cfg" file found in the files repository to your nagios server and reload
- Optionaly if you use Puppet there is an "http_command.pp" file which will generate the configuration for you

## Installing the web interface 

There is a limited web interface made with Silex. It uses by default a Mysql database and should work out of the box.

- Configure your favorite webserver to point to web/public and set up some ACL
- Create a database and inject the schema found in sql/schema.sql
- Copy the /web/config.yml.dist to /web/config.yml with your database name / user / login
- Run composer in the web repository

## Custom usage

Check the web and tests bootstrap files to see how you can adapt the service to your own application.

Basically it goes like this:

```
<?php

// Access a PDO Db ( or any data source )
$db = new \PDO();

// Instanciate a DataAccessor that reads/write your data source
$dataAccessor = new DataAccessor( $db );

// Instanciate an HttpcheckService with your DataAccessor
$httpcheckService = new Service( $dataAccessor );

// Use the httpcheckService to CRUD your HTTP checks
...

```


## Running tests with PHPUnit

There is a bunch of tests (not enough to cover everything, though) 

To run them : 

- Run composer in the tests repository
- Create a test database and inject the schema found in sql/schema.sql
- Copy the /tests/config.yml.dist to /tests/config.yml with your database name / user / login
- Run PHPUnit at project root to use the default phpunit.xml file

## Status

The project could use some more features:

- **Logging** 
- **ACL**

Help is welcome.

## License

GPL v3