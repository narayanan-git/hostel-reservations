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
             VALUES(?, ?, ?, ?, NOW())");
        $stmt->bind_param('ssss', $firstname, $lastname, $referrer, $createdBy);
        
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

    /**
     * Searches for a set of bookings given the specified criterion.
     * $minDate : earliest date (DateTime)
     * $maxDate : latest date (DateTime)
     * $dateMatchType : type of dates to apply search for (one of 'checkin', 'creation', 'reserved')
     * $resourceId : id of resource to match (optional)
     * $status : one of 'reserved', 'checkedin', 'checkedout', 'cancelled' (optional)
     * $matchName : (partial) name to match (case-insensitive) (optional)
     * $startRow : row to start returning records (default 0)
     * $maxRows : maximum number of bookings to return (default 20)
     * Returns array() of BookingSummary
     */
    static function getBookingsForDateRange($minDate, $maxDate, $dateMatchType, $resourceId = null, $status = null, $matchName = null, $startRow = 0, $maxRows = 20) {
        global $wpdb;

        $matchNameSql = $matchName == null ? null : '%'.strtolower($matchName).'%';
        
        // match date by first date on any allocation (there could be multiple checkin dates for a single booking!)
        if ($dateMatchType == 'checkin') {
            $sql = "SELECT DISTINCT booking_id, booking_id, firstname, lastname, referrer, created_by, created_date
                    FROM (
                        SELECT bk.booking_id, bk.firstname, bk.lastname, bk.referrer, bk.created_by, bk.created_date,
                               al.allocation_id,
                               MIN(bd.booking_date) checkin_date
                          FROM ".$wpdb->prefix."booking bk
                          JOIN ".$wpdb->prefix."allocation al ON bk.booking_id = al.booking_id
                          JOIN ".$wpdb->prefix."bookingdates bd ON bd.allocation_id = al.allocation_id
                         WHERE 1 = 1 
                               ".($resourceId == null ? "" : " AND al.resource_id = %d")."
                               ".($status == 'all' ? "" : " AND al.status = %s")."
                               ".($matchNameSql == null ? "" : 
                                    " AND (LOWER(bk.firstname) LIKE %s OR
                                           LOWER(bk.lastname) LIKE %s OR
                                           LOWER(al.guest_name) LIKE %s)")."
                         GROUP BY bk.booking_id, al.allocation_id
                    ) t
                    WHERE checkin_date BETWEEN STR_TO_DATE(%s, '%%d.%%m.%%Y') AND STR_TO_DATE(%s, '%%d.%%m.%%Y')
                    ORDER BY booking_id
                    LIMIT %d, %d";
        }
        
        // any booking_date falls within the given date range
        if ($dateMatchType == 'reserved') {
            $sql = "SELECT DISTINCT bk.booking_id, bk.firstname, bk.lastname, bk.referrer, bk.created_by, bk.created_date
                    FROM ".$wpdb->prefix."booking bk
                    JOIN ".$wpdb->prefix."allocation al ON bk.booking_id = al.booking_id
                    JOIN ".$wpdb->prefix."bookingdates bd ON bd.allocation_id = al.allocation_id
                   WHERE 1 = 1
                         ".($resourceId == null ? "" : "AND al.resource_id = %d")."
                         ".($status == 'all' ? "" : "AND al.status = %s")."
                         ".($matchNameSql == null ? "" : 
                            " AND (LOWER(bk.firstname) LIKE %s OR
                                   LOWER(bk.lastname) LIKE %s OR
                                   LOWER(al.guest_name) LIKE %s)")."
                     AND bd.booking_date BETWEEN STR_TO_DATE(%s, '%%d.%%m.%%Y') AND STR_TO_DATE(%s, '%%d.%%m.%%Y')
                   ORDER BY bk.booking_id
                   LIMIT %d, %d";
        }
        
        if ($dateMatchType == 'creation') {
            $sql = "SELECT bk.booking_id, bk.firstname, bk.lastname, bk.referrer, bk.created_by, bk.created_date
                      FROM ".$wpdb->prefix."booking bk
                      JOIN ".$wpdb->prefix."allocation al ON bk.booking_id = al.booking_id
                     WHERE 1 = 1
                           ".($resourceId == null ? "" : "AND al.resource_id = %d")."
                           ".($status == 'all' ? "" : "AND al.status = %s")."
                           ".($matchNameSql == null ? "" : 
                             " AND (LOWER(bk.firstname) LIKE %s OR
                                    LOWER(bk.lastname) LIKE %s OR
                                    LOWER(al.guest_name) LIKE %s)")."
                       AND bk.created_date BETWEEN STR_TO_DATE(%s, '%%d.%%m.%%Y') AND STR_TO_DATE(%s, '%%d.%%m.%%Y')
                     ORDER BY bk.booking_id";
        }

        if (false === isset($sql)) {
            throw new Exception("Unsupported value for date match type $dateMatchType");
        }

        // the sql parameters are in the same order independent of the query stored in $sql
        $sqlparams = array();
        if ($resourceId != null) {
            $sqlparams[] = $resourceId;
        }
        if ($status != 'all') {
            $sqlparams[] = $status;
        }
        if ($matchNameSql != null) {
            $sqlparams[] = $matchNameSql;
            $sqlparams[] = $matchNameSql;
            $sqlparams[] = $matchNameSql;
        }
        $sqlparams[] = $minDate->format('d.m.Y');
        $sqlparams[] = $maxDate->format('d.m.Y');
        $sqlparams[] = $startRow;
        $sqlparams[] = $maxRows;
debuge($sql, $sqlparams);
        // execute our query 
        $resultset = $wpdb->get_results($wpdb->prepare($sql, $sqlparams));
        
        if($wpdb->last_error) {
            error_log("Failed to execute query " . $wpdb->last_query);
            throw new DatabaseException($wpdb->last_error);
        }
        $result = array();
        foreach ($resultset as $res) {
            $result[$res->booking_id] = new BookingSummary(
                $res->booking_id, 
                $res->firstname, 
                $res->lastname, 
                $res->referrer, 
                $res->created_by, 
                new DateTime($res->created_date));
            $result[$res->booking_id]->guests = 
                AllocationDBO::fetchGuestNamesForBookingId($res->booking_id);
            $result[$res->booking_id]->statuses = 
                AllocationDBO::fetchStatusesForBookingId($res->booking_id);
            $result[$res->booking_id]->resources = 
                ResourceDBO::fetchResourcesForBookingId($res->booking_id);
            $result[$res->booking_id]->bookingDates = 
                AllocationDBO::fetchDatesForBookingId($res->booking_id);
        }
debuge($result);
        return $result;
    }
    
}

?>