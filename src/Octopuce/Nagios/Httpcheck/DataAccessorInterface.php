<?php

namespace Octopuce\Nagios\Httpcheck;

/**
 * Description of Interface
 *
 * @author alban
 */
interface DataAccessorInterface {

    public function deleteHttpcheck($id);

    public function findHttpcheck($id);
    
    public function saveHttpcheck($data);

    public function getHttpcheckList($options);

    public function getIsUpdateRequired($options);

    public function saveService($command_line, $check_id);

    public function getAllServiceContent($only_alerts);

    public function getHttpcheckServiceList($param);
}
