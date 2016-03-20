<?php

namespace Octopuce\Nagios\Httpcheck;


/**
 * Description of RemoteSyncManager
 *
 * @author alban
 */
class RemoteConfigManager {
    
    /** @var string */
    var $log_file = "/var/log/nagios3/sync_ubal.log";
    /** @var array */
    var $sourceDestList = array();
    /** @var boolean */
    var $reload_nagios = false;
    /** @var int */
    var $log_level = 0; // By default, log nothing
    /** @var string */
    var $tmp_file_path = "/tmp/sync-httpcheck-cron-";
    
    /**
     * 
     * @param type $options
     * @throws type
     */
    function __construct( $options = array() ){
        
	// Attempts to retrieve log_level
	if( array_key_exists( "log_level", $options )) {
	    $this->log_level = $options["log_level"];
	}
        
	// Attempts to retrieve log_file
        if (array_key_exists("log_file", $options) && !is_null($options["log_file"])) {
            $this->log_file = $options["log_file"];
        }
        
	// It should retrieve sourceDestList
	if( array_key_exists( "sourceDestList", $options ) ) {
	    $this->sourceDestList = $options["sourceDestList"];
	}else{
	    throw Exception ( "Failed to retrieve sourceDestList");
	}
        
        // Attempts to retrieve reload_nagios
        if (array_key_exists("reload_nagios", $options) && !is_null($options["reload_nagios"])) {
            $this->reload_nagios = $options["reload_nagios"];
        }
        
        // It should retrieve tmp_file_path
        if (array_key_exists("tmp_file_path", $options) && !is_null($options["tmp_file_path"])) {
            $this->tmp_file_path = $options["tmp_file_path"];
        } 
        
	$this->log( "Start with sourceDestList ".print_r($this->sourceDestList,1) , 3);
	$this->log( "Log level: ".$this->log_level , 3);

    }

    /**
     * Basic log : higher log level = more debug
     *
     * @param string $message
     * @throws Exception
     */
    function log( $message, $level = 3 ){
	// Do nothing ?
	if( $this->log_level < $level ){
	    return;
	}
        if( !is_file( $this->log_file)){
            if( !touch( $this->log_file)){
                return;
            }
        }
        if( !is_string($message)){
            $message = json_encode($message);
        }
	$horodated_message = date("c")." : ${message} \n";
	if( ! file_put_contents($this->log_file, $horodated_message, FILE_APPEND) ){
	    throw new Exception("Failed to write { $horodated_message } into log file ".$this->log_file);
	}
    }

    /**
     * 
     * @throws \InvalidArgumentException
     * @throws Exception
     */
    function doSync(){

	// For each source
	foreach( $this->sourceDestList as $transportInfo ){
            
            // Attempts to retrieve source
            if (array_key_exists("source", $transportInfo) && !is_null($transportInfo["source"])) {
                $source = $transportInfo["source"];
            } else {
                throw new \InvalidArgumentException("Missing parameter source");
            }
            // Attempts to retrieve target
            if (array_key_exists("target", $transportInfo) && !is_null($transportInfo["target"])) {
                $target = $transportInfo["target"];
            } else {
                throw new \InvalidArgumentException("Missing parameter target");
            }
            // Attempts to retrieve transport
            if (array_key_exists("transport", $transportInfo) && !is_null($transportInfo["transport"])) {
                $transport = $transportInfo["transport"];
            } else {
                throw new \InvalidArgumentException("Missing parameter transport");
            }

	    $this->log( "run $source => $target",3);

            $tmp_file_path = $this->tmp_file_path.time();
            
	    // It should attempt to retrieve result
            switch ($transport) {
                case "http":
                    $out = file_get_contents($source);
                    file_put_contents($tmp_file_path, $out);
                    break;
                case "ssh":
                    // Attempts to retrieve ssh_command_line
                    if (array_key_exists("ssh_command_line", $options) && !is_null($options["ssh_command_line"])) {
                        $ssh_command_line = $options["ssh_command_line"];
                    } else {
                        throw new \InvalidArgumentException("Missing parameter ssh_command_line");
                    }
                    $out = $this->ssh($ssh_command_line, $source, $tmp_file_path);
                    break;
                default:
                    throw new \InvalidArgumentException("Missing parameter transport");
                break;
            }
            
	    // check if different from dest
            $shell_target = escapeshellarg( $target );
            $command = "diff $shell_target '$tmp_file_path'";
            exec( $command, $out, $code );
            if( $code == 0 ) {
		unlink( $tmp_file_path );
                continue;
            }
	    $this->log("Replacing $target",1);
	    $this->log("Differences with $target : ".print_r($out,1),2);

	    if( is_file( $target ) ) {
		if( ! unlink( $target ) ){
		    throw new Exception( "Failed to unling $target ");
		}
	    }else{
		$this->log( "Seems the target file $target was removed",2);
	    }
	    // Copy to dest
            if( ! rename( $tmp_file_path, $target ) ){

		$this->log( "Failed to save $target",1);
	    }
	    
            // Set reload
            $this->reload_nagios = true;
            
	}
        
	// Reload if you must
	if( $this->reload_nagios ){
	    $this->log( "Restarting nagios", 1 );
 	    exec( "/usr/sbin/service nagios3 reload", $nagios_output, $code);
             if( $code != 0 ){
                 throw new Exception("Failed to reload Nagios : $nagios_output");
             }
	}
    }

    /**
     * 
     * @param string $ssh_command_line 
     *  Ex: "ssh -i /path/to/id.rsa user@server"
     * @param string $orig_command 
     *  Ex: "/usr/bin/php /path/to/script.php alert"
     * @param string $tmp_file_path
     * @return boolean
     * @throws Exception
     */
    function ssh( $ssh_command_line, $orig_command, $tmp_file_path  ){

	$command = escapeshellarg( $orig_command );
	$path = escapeshellarg( $tmp_file_path );
	$command = "$ssh_command_line $command > $path ";
	$this->log( "SSH command $command", 3);
	exec( $command, $out, $code );
	if( $code != 0 ) {
	    throw new Exception("ssh error code $code for command $command");
	}
	$fileArray = file($tmp_file_path);
	if( count( $fileArray ) < 10 ){
	    throw new Exception("The command $command got too few lines : ".print_r($fileArray, 1) );
	}
        return true;
    }

}

