<?php

namespace Octopuce\Nagios\Httpcheck;

/**
 * Implements for Mysql the DataAccessorInterface
 *
 * @author alban
 */
class DefaultDataAccessor implements \Octopuce\Nagios\Httpcheck\DataAccessorInterface {

    /**
     * @var string
     */
    protected $db_table_services = "nagios_httpcheck_service";

    /**
     * @var string
     */
    protected $db_table_check = "nagios_httpcheck";

    /**
     *
     * @var \PDO
     */
    protected $db;

    /**
     *
     * @param type $db
     */
    public function __construct(\PDO $db, $options = array()) {

        // It should use PDO
        $this->db = $db;

        // Attempts to retrieve db_table
        if (array_key_exists("db_table", $options) && !is_null($options["db_table"])) {
            $this->db_table_check = $options["db_table"];
        }

        // Attempts to retrieve db_table_services
        if (array_key_exists("db_table_services", $options) && !is_null($options["db_table_services"])) {
            $this->db_table_services = $options["db_table_services"];
        }
    }
    
    public function deleteHttpcheck($id) {
       
        $query = "DELETE FROM " . $this->db_table_check . " WHERE id = ?";
        $rawPdoStatement = $this->db->prepare($query);
        $return = $rawPdoStatement->execute(array( $id ));
        return $return;
    }

    public function findHttpcheck( $id ) {
        
        $query = "SELECT h.* FROM " . $this->db_table_check . " h WHERE id = ?;";
        $rawPdoStatement = $this->db->prepare($query);
        $success = $rawPdoStatement->execute(array( $id ));
        if ($success) {
            $return = $rawPdoStatement->fetch();
        }
        return $return;

    }
    
    /**
     * Returns a list of http checks
     * $filter is an array containing a list of filter to apply :
     *
     * @param array $options
     * @return array
     */
    public function getHttpcheckList($options) {

        // It should use an array as output
        $returnList = array();
        if (!is_array($options)) {
            $options = array();
        }
        if (!isset($options["offset"]) || !intval($options["offset"])) {
            $options["offset"] = 0;
        }
        if (!isset($options["count"]) || !intval($options["count"])) {
            $options["count"] = 50;
        }
        $params = array();
        $where = "";
        $from = "";
        $select = "";
        $query = "SELECT h.* $select FROM " . $this->db_table_check . " h $from WHERE 1 $where;";
        $rawPdoStatement = $this->db->prepare($query);
        $success = $rawPdoStatement->execute($params);
        if ($success) {
            $returnList = $rawPdoStatement->fetchAll();
        }
        return $returnList;
    }

    public function getAllServiceContent($only_alerts) {

        // It should use a where condition
        $where = "";
        
        // It should add the where for alert flags
        if ($only_alerts) {
            $where = "WHERE h.no_alert = 0";
        }

        // It should retrieve the content
        $query = ""
        ." SELECT s.command_line "
        ." FROM ".$this->db_table_check." h "
        ." LEFT JOIN " . $this->db_table_services . " s ON h.id = s.httpcheck_id "
        .$where;
        
        $rawPdoStatement = $this->db->prepare($query);
        $result = $rawPdoStatement->execute();
        if( $result === false ){
            return false;
        }
        $checkList = $rawPdoStatement->fetchAll();
        $all_checks = "";
        foreach ($checkList as $row) {
            $all_checks .= $row["command_line"] . "\n";
        }
        return $all_checks;
    }


    public function getHttpcheckServiceList($options) {
        
        $bindValues = array();
        $returnList = array();
        $where = "";
        
        // Attempts to retrieve where
        if (array_key_exists("where", $options) && is_array($options["where"]) && $options["where"] ) {
            foreach( $options["where"] as $where => $value) {
                $bindValues[] = $value;
            }
            $where = implode(" AND ", $options["where"]);
        } 
        
        $query = "" 
        ." SELECT h.* "
        ." FROM ".$this->db_table_check." h "
        ." LEFT JOIN " . $this->db_table_services. " s ON h.id = s.httpcheck_id "
        ." WHERE $where  ";
        $rawPdoStatement = $this->db->prepare($query);
        $success = $rawPdoStatement->execute($bindValues);
        if ($success) {
            $returnList = $rawPdoStatement->fetchAll();
        }
        return $returnList;
    }
    
    
    public function getIsUpdateRequired($last_update_ts) {
        $query = "
        SELECT COUNT(nh.id)
        FROM `" . $this->db_table_check . "` nh
        LEFT JOIN `" . $this->db_table_services . "` nhs ON nh.id = nhs.httpcheck_id
        WHERE updated_at > FROM_UNIXTIME(?)
            OR command_line IS NULL";
        $rawPdoStatement = $this->db->prepare($query);
        $pdoStatement = $rawPdoStatement->execute(array($last_update_ts));
        $res = $rawPdoStatement->fetchColumn();
        return( $res > 0 );
    }

    public function saveService($command_line, $check_id) {

        $query = " REPLACE INTO `" . $this->db_table_services . "`
                (`command_line`,
                `httpcheck_id`)
                VALUES ( ?, ? ) ;";

        $rawPdoStatement = $this->db->prepare($query);
        $pdoStatement = $rawPdoStatement->execute(array($command_line, $check_id));
        if (!$pdoStatement) {
            throw new Exception(_("Db error occured when saving new Service"));
        }
        return true;
    }
    public function saveHttpcheck($data) {

        $bindValuesList = array();

        $fieldList = array("fqdn", "ip", "host", "uri", "port", "status", "ssl", "regexp", "invert_regexp", "login", "pass", "no_alert", "created_at");
        foreach ($fieldList as $field) {
            if (isset($data[$field])) {
                $bindValuesList[$field] = $data[$field];
            } else {
                $bindValuesList[$field] = "";
            }
        }
        if (array_key_exists("id", $data) && !is_null($data["id"])) {
            $id = $data["id"];
            unset($data['id']);
            return $this->updateCheck($data, array('id = ?' => $id));
        } else {
            unset($data['id']);
            return $this->insertCheck($data);
        }
    }

    public function insertCheck($data) {
        
        // It should build the column list
        $columnList = array_keys($data);
        array_walk($columnList, function (&$val){
            $val = "`$val`"; 
        });
        $columnList[] = "`created_at`"; 
        
        // It should build the value list
        $valueList = array();
        foreach( $data as $val ){
            $valueList[] = "?";
        }
        $valueList[] = "NOW()";
        
        $statement = $this->db->prepare(
            'INSERT INTO `' . $this->db_table_check . '` ('
            . implode(',', $columnList)
            .') VALUES ('
            . implode(",", $valueList)
            .')');
        $result = $statement->execute(array_values($data));
        if( $result ){
            return $this->db->lastInsertId();
        }
        return false;

    }

    /**
     * 
     * @param array $data
     * @param type $where
     * @return boolean
     */
    public function updateCheck($data, $whereList) {

        $columnList = array_keys($data);
        array_walk($columnList, function (&$val){
            $val = " `$val` = ? "; 
        });
        $statement = $this->db->prepare(
            ' UPDATE `' . $this->db_table_check . '` '
            .' SET '
            . implode(',', $columnList)
            .' WHERE '
            . implode( " AND ", array_keys( $whereList) ) 
        );
        $bindValueList = array_merge(array_values($data), array_values($whereList));
        $result = $statement->execute($bindValueList);
        if( $result ){
            return true;
        }
        return false;

    }

}
