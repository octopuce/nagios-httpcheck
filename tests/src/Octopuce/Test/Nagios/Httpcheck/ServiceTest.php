<?php

namespace Octopuce\Test\Nagios\Httpcheck;

use Octopuce\Nagios\Httpcheck\Service as Service;
use Octopuce\Nagios\Httpcheck\DefaultDataAccessor as DefaultDataAccessor;

/**
 * This test uses the Default DataAccessor 
 * 
 * Feel free to hack your own DataAccessor in to test it.
 */
class ServiceTest extends \PHPUnit_Extensions_Database_TestCase {

    /** @var string */
    protected $update_file_location = "/tmp/test_last_updated";

    /** @var string */
    protected $datasetsPath;

    /** @var \PDO */
    protected $db;

    /** @var \PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection */
    protected $mockupDb;

    /** @var \Octopuce\Nagios\Httpcheck\Service */
    protected $object;

    /** @var \Octopuce\Nagios\Httpcheck\DataAccessorInterface */
    protected $dataAccessor;
    
    protected $DataSetList = array();

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::deleteCheck
     */
    public function testDeleteCheck() {
        
        $result = $this->object->deleteCheck(1);
        $this->assertTrue($result,"It should succeed to delete a row in Data Storage");
        $this->assertEquals(0, count($this->object->getHttpcheckList(array())),"There should be no row in Data Storage");
        
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::findHttpcheck
     */
    public function testFindCheck() {
        $result = $this->object->deleteCheck(1);
        $this->assertTrue($result);
        $this->assertEquals(0, count($this->object->getHttpcheckList(array())));
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::getHttpcheckList
     */
    public function testGetList() {
        $result = $this->object->getHttpcheckList(array());
        $this->assertCount(2, $result);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::saveHttpcheck
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::saveHttpcheck
     */
    public function testSaveHttpcheck( $httpcheck, $message) {
        $result = $this->object->saveHttpcheck($httpcheck);
        $this->assertEquals(1, $result);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::getNagiosServiceContent
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::getNagiosServiceContent
     */
    public function testGetNagiosServiceContent( $httpcheck, $expected, $message) {

        $result = $this->object->getNagiosServiceContent($httpcheck);
        $this->assertEquals(trim($expected), trim($result), $message);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::getServiceData
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::getServiceData
     */
    public function testGetServiceData($httpcheck, $expected, $message) {
        
        $result = $this->object->getServiceData($httpcheck);
        $this->assertEquals(json_encode($expected,JSON_NUMERIC_CHECK), json_encode($result,JSON_NUMERIC_CHECK), $message);

    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::isUpdateRequired
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::isUpdateRequired
     */
    public function testIsUpdateRequired($statusFileInfo, $expected, $message) {

        $this->createLastUpdatedFile($statusFileInfo["age"], $statusFileInfo["content"]);
        $result = $this->object->isUpdateRequired();
        $this->assertEquals($result,$expected, $message);
    }
    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::getLastUpdateTimestamp
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::getLastUpdateTimestamp
     */
    public function testGetLastUpdateTimestamp($statusFileInfo, $expected, $message ) {
        $this->createLastUpdatedFile($statusFileInfo["age"], $statusFileInfo["content"]);
        $result = $this->object->getLastUpdateTimestamp();
        $this->assertEquals($result,$expected, $message);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::getLastUpdateMtime
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::getLastUpdateMtime
     */
    public function testGetLastUpdateMtime($statusFileInfo, $expected, $message) {
        $this->createLastUpdatedFile($statusFileInfo["age"], $statusFileInfo["content"]);
        $result = $this->object->getLastUpdateMtime();
        $this->assertEquals($result,$expected, $message);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::setNewUpdateTimestamp
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::setNewUpdateTimestamp
     */
    public function testSetNewUpdateTimestamp( $statusFileInfo, $expected, $message ) {
        $result = $this->object->setNewUpdateTimestamp($statusFileInfo["content"]);
        $this->assertEquals($result,true, "It should return true");
        $this->assertEquals(file_get_contents($this->update_file_location),$expected, $message);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::setNewUpdateMtime
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::setNewUpdateMtime
     */
    public function testSetNewUpdateMtime($statusFileInfo, $expected, $message) {
        $result = $this->object->setNewUpdateMtime($statusFileInfo["age"]);
        $this->assertEquals($result,true, "It should return true");
        $this->assertEquals(filemtime($this->update_file_location),$expected, $message);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::saveService
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::saveService
     */
    public function testSaveService( $service, $expected, $message ) {
        $result = $this->object->saveService($service);
        $this->assertEquals($result, $expected, $message);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::updateAllServices
     */
    public function testUpdateAllServices() {
        $result = $this->object->updateAllServices();
        $this->assertEquals($result, true, "It should update all services");
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::getAllForNagios
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::getAllForNagiosWorks
     */
    function testGetAllForNagios($statusFileInfo, $expected, $message) {

        $this->createLastUpdatedFile($statusFileInfo["age"], $statusFileInfo["content"]);
        $result = $this->object->getAllForNagios();
        $this->assertEquals(trim($result), trim($expected), $message);
    }

    /**
     * @covers Octopuce\Nagios\Httpcheck\Service::handleRequest
     * @dataProvider \Octopuce\Test\Nagios\Httpcheck\ServiceProvider::handleRequest
     */
    public function testHandleRequest( $request, $expected, $message ) {
        $result = $this->object->handleRequest($request);
        ksort($request);
        ksort($result);
        $this->assertEquals(json_encode($result,JSON_NUMERIC_CHECK), json_encode($expected,JSON_NUMERIC_CHECK), $message);
        
    }

    /*
     * 
     */

    public function setUp() {
        $this->datasetsPath = APP_PATH . "/datasets/";
        $this->DataSetList = array(
            "testGetAllForNagios" => array(
                0 => "testGetAllForNagiosWorks-00.yml",
                1 => "testGetAllForNagiosWorks-01.yml",
            ),
            "testFindCheck" => "testFindCheck.yml",
            "testDeleteCheck" => "testDeleteCheck.yml",
            "testGetList" => "testGetList.yml",
            "testSaveHttpcheck" => "testSaveHttpcheck.yml",
            "testGetNagiosServiceContent" => "testGetNagiosServiceContent.yml",
            "testGetServiceData" => "testGetServiceData.yml",
            "testIsUpdateRequired" => "testIsUpdateRequired.yml",
            "testGetLastUpdateTimestamp" => "testGetLastUpdateTimestamp.yml",
            "testGetLastUpdateMtime" => "testGetLastUpdateMtime.yml",
            "testSetNewUpdateTimestamp" => "testSetNewUpdateTimestamp.yml",
            "testSetNewUpdateMtime" => "testSetNewUpdateMtime.yml",
            "testSaveService" => "testSaveService.yml",
            "testUpdateAllServices" => "testUpdateAllServices.yml",
            "testLog" => "testLog.yml",
            "testHandleRequest" => "testHandleRequest.yml",
        );
        touch($this->update_file_location);

        parent::setUp();
    }

    protected function tearDown() {
        parent::tearDown();
        if (is_file($this->update_file_location)) {
            unlink($this->update_file_location);
        }
    }

    /**
     * 
     * @return PHPUnit_Extensions_Database_DB_DefaultDatabaseConnection
     */
    protected function getConnection() {

        global $dbConfig;
        if (!$this->db) {
            $this->db = new \PDO("mysql:host=" . $dbConfig["host"] . ";dbname=" . $dbConfig["dbname"], $dbConfig["user"], $dbConfig["pass"]);
        }
        $this->dataAccessor = new DefaultDataAccessor($this->db);
        $this->object = new Service($this->dataAccessor, array("update_file_location" => $this->update_file_location));
        $this->mockupDb =  $this->createDefaultDBConnection($this->db);
        return $this->mockupDb;
    }

    /**
     * 
     * @return PHPUnit_Extensions_Database_DataSet_AbstractDataSet
     */
    protected function getDataSet() {

        // It should retrieve the test name
        $testName = $this->getName(false);

        // It should load a default dataset if not a dataset based test
        if (!array_key_exists($testName, $this->DataSetList)) {

            $dataset = new \PHPUnit_Extensions_Database_DataSet_DefaultDataSet();
        } else {
            // It should load a custom dataset 
            if (is_array($this->DataSetList[$testName])) {
                $dataName = $this->getName(true);
                preg_match("/^.*#(\d+)$/", $dataName, $matches);
                $testNumber = $matches[1];
                if (!isset($this->DataSetList[$testName][$testNumber])) {
                    throw new \Exception("Missing $testNumber for $testName Provider");
                }
                $dataset_file = $this->DataSetList[$testName][$testNumber];
            } else {
                $dataset_file = $this->DataSetList[$testName];
            }
            $dataset = $this->loadDataSet($dataset_file);
        }
        return $dataset;

        // Always exit with a valid dataset
        return $dataset;
    }

    /**
     * 
     * @param string $fileList
     * @return \PHPUnit_Extensions_Database_DataSet_YamlDataSet
     * @throws \Exception
     */
    public function loadDataSet($fileList) {
        if (empty($fileList)) {
            throw new \Exception("No files specified");
        }
        if (!is_array($fileList)) {
            $fileList = array($fileList);
        }
        $datasetList = array();
        foreach ($fileList as $file_name) {
            $file = $this->datasetsPath . "/$file_name";
            if (!is_file($file)) {
                throw new \Exception("missing $file");
            }
            $dataSet = new \PHPUnit_Extensions_Database_DataSet_YamlDataSet($file);
            $datasetList[] = $dataSet;
        }
        $compositeDataSet = new \PHPUnit_Extensions_Database_DataSet_CompositeDataSet($datasetList);
        return $compositeDataSet;
    }

    /**
     * 
     * @param type $dataset
     * @return \PHPUnit_Extensions_Database_DataSet_YamlDataSet
     */
    protected function buildYamlDataSet($dataset) {
        return new PHPUnit_Extensions_Database_DataSet_YamlDataSet(
                $this->datasetsPath . $dataset
        );
    }

    /**
     * Creates files with set file age and content
     * 
     * @param array $fileList
     */
    function createLastUpdatedFile($age_ts, $content) {
        if (!file_put_contents($this->update_file_location, $content)) {
            throw new Exception("Failed to write into " . $this->update_file_location);
        }
        if (!touch($this->update_file_location, $age_ts, $age_ts)) {
            throw new Exception("Failed set date on " . $this->update_file_location);
        }
    }

}
