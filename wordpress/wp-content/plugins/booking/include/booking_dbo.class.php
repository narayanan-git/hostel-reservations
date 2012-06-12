<?php

/**
 * Database object for booking table.
 */
class BookingDBO {

    /**
     * Inserts a new booking entry into the booking table.
     * $mysqli : manual db connection (for transaction handling)
     * $firstname : first name (required)
     * $lastname : last name (optional)
     * $referrer : hostelworld, hostelbookers, walkin, phone, etc... (optional)
     * $createdBy : user id of person making this booking
     * Returns id of inserted booking id
     */
    static function insertBooking($mysqli, $firstname, $lastname, $referrer, $createdBy) {
    
        global $wpdb;
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."booking(firstname, lastname, referrer, created_by, created_date)
             VALUES(?, ?, ?, ?, STR_TO_DATE(?, '%m.%d.%Y'))");
             
        $now = new DateTime();
        $stmt->bind_param('sssss', $firstname, $lastname, $referrer, $createdBy, $now->format('m.d.Y'));
        
        if(FALSE === $stmt->execute()) {
            throw new DatabaseException("Error during INSERT: " . $mysqli->error);
        }
        $stmt->close();

        return $mysqli->insert_id;
        
//        $wpdb->insert($wpdb->prefix ."booking", 
//             array( 'firstname' => $firstname, 
//                    'lastname' => $lastname, 
//                    'referrer' => $referrer, 
//                    'created_by' => $createdBy,
//                    'created_date' => new DateTime()));
//        return $wpdb->insert_id;
    }
}

?>