<?php

/**
 * Encapsulated db connection to handle transactions manually.
 * Autocommit is disabled by default.
 * Required for now since wordpress 3.2 does not support transactions.
 */
class DbTransaction {

    var $mysqli;   // db connection singleton

    function __construct() {
        global $wpdb;

        // connect using the same info as wordpress
        $this->mysqli = new mysqli( $wpdb->dbhost, $wpdb->dbuser, $wpdb->dbpassword, $wpdb->dbname );
        
        if ($this->mysqli->connect_errno) {
            throw new DatabaseException("Failed to connect to MySQL: " . $this->mysqli->connect_error);
        }        
        
        // cancel auto commit option in the database
        $this->mysqli->autocommit(FALSE);
error_log("finished constructing dbtransaction");
    }
}

?>