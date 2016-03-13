<?php

namespace Octopuce\Nagios\Httpcheck;


/**
 * Description of RemoteSyncManager
 *
 * @author alban
 */
class RemoteConfigManager {
    
    var $log_file = "/var/log/nagios3/sync_ubal.log";
    var $sourceDestList = array();
    var $must_reload = false;
    var $log_level = 0; // By default, log nothing
    var $tmp_file_path = "/tmp/sync-benedict-ubal-cron-";
    /**
     * 
     * @param type $options
     * @throws type
     */
    function __construct( $options = array() ){
	if( array_key_exists( "log_level", $options )) {
	    $this->log_level = $options["log_level"];
	}
	if( array_key_exists( "sourceDestList", $options ) ) {
	    $this->sourceDestList = $options["sourceDestList"];
	}else{
	    throw Exception ( "Failed to retrieve sourceDestList");
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
	$horodated_message = date("c")." : ${message} \n";
	if( ! file_put_contents($this->log_file, $horodated_message, FILE_APPEND) ){
	    throw new Exception("Failed to write { $horodated_message } into log file ".$this->log_file);
	}
    }

    /**
     * 
     * @throws Exception
     */
    function doSync(){

	// For each source
	foreach( $this->sourceDestList as $source => $destination ){

	    $this->log( "run $source => $destination",3);

            $tmp_file_path = $this->tmp_file_path.time();

	    // run script and retrieve result
            $out = $this->ssh($source, $tmp_file_path);
            
	    // check if different from dest
            $shell_destination = escapeshellarg( $destination );
            $command = "diff $shell_destination '$tmp_file_path'";
            exec( $command, $out, $code );
            if( $code == 0 ) {
		unlink( $tmp_file_path );
                continue;
            }
	    $this->log("Replacing $destination",1);
	    $this->log("Differences with $destination : ".print_r($out,1),2);

	    if( is_file( $destination ) ) {
		if( ! unlink( $destination ) ){
		    throw new Exception( "Failed to unling $destination ");
		}
	    }else{
		$this->log( "Seems the destination file $destination was removed",2);
	    }
	    // Copy to dest
            if( ! rename( $tmp_file_path, $destination ) ){

		$this->log( "Failed to save $destination",1);
	    }
	    
            // Set reload
            $this->must_reload = true;
            
	}
        
	// Reload if you must
	if( $this->must_reload ){
	    $this->log( "Restarting nagios", 1 );
 	    exec( "/usr/sbin/service nagios3 reload", $nagios_output, $code);
             if( $code != 0 ){
                 throw new Exception("Failed to reload Nagios : $nagios_output");
             }
	}
    }

    function ssh( $orig_command, $tmp_file_path  ){

	$command = escapeshellarg( $orig_command );
	$path = escapeshellarg( $tmp_file_path );
	$command = "ssh ubal.octopuce.fr -a -i /root/.ssh/id_dsa $command > $path ";
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

