<?php

/**
 * Database object for booking table.
 */
class BookingDBO {

    /**
     * Returns booking details for the given booking id.
     * $bookingId : existing booking id to edit
     * Returns booking recordset
     */
    static function fetchBookingDetails($bookingId) {
        global $wpdb;
        
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT booking_id, firstname, lastname, referrer
               FROM ".$wpdb->prefix."booking b
              WHERE b.booking_id = %d", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        if(empty($resultset)) {
            throw new DatabaseException("$bookingId doesn't exist!");
        }
        
        // there should only be one match by primary key
        foreach ($resultset as $res) {
            return $res;
        }
    }

    /**
     * Inserts a new booking entry into the booking table.
     * $mysqli : manual db connection (for transaction handling)
     * $firstname : first name (required)
     * $lastname : last name (optional)
     * $referrer : hostelworld, hostelbookers, walkin, phone, etc... (optional)
     * Returns id of inserted booking id
     * Throws DatabaseException on insert error
     */
    static function insertBooking($mysqli, $firstname, $lastname, $referrer) {
    
        global $wpdb;
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."booking(firstname, lastname, referrer, created_by, created_date, last_updated_by, last_updated_date)
             VALUES(?, ?, ?, ?, NOW(), ?, NOW())");
        $userLogin = wp_get_current_user()->user_login;
        $stmt->bind_param('sssss', $firstname, $lastname, $referrer, $userLogin, $userLogin);
        
        if(false === $stmt->execute()) {
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
     * Updates an existing booking entry.
     * $mysqli : manual db connection (for transaction handling)
     * $bookingId : id of booking to update
     * $firstname : first name (required)
     * $lastname : last name (optional)
     * $referrer : hostelworld, hostelbookers, walkin, phone, etc... (optional)
     * Throws DatabaseException on update error
     */
    static function updateBooking($mysqli, $bookingId, $firstname, $lastname, $referrer) {
        global $wpdb;
error_log("updateBooking $bookingId, $firstname, $lastname, $referrer");

        // first check what has changed
        $bookingRs = self::fetchBookingDetails($bookingId);
        $auditMsgs = array();
        
        if ($firstname !== $bookingRs->firstname || $lastname !== $bookingRs->lastname) {
            $auditMsgs[] = "Changing name from $bookingRs->firstname $bookingRs->lastname to $firstname $lastname";
        }
        if ($referrer !== $bookingRs->referrer) {
            $auditMsgs[] = "Changing referrer from $bookingRs->referrer to $referrer";
        }
error_log(" is changed? auditMsg ".sizeof($auditMsgs));

        // only update if something has changed
        if (sizeof($auditMsgs) > 0) {
            $stmt = $mysqli->prepare(
                "UPDATE ".$wpdb->prefix."booking
                    SET firstname = ?,
                        lastname = ?,
                        referrer = ?,
                        last_updated_by = ?,
                        last_updated_date = NOW()
                WHERE booking_id = ?");
            $userLogin = wp_get_current_user()->user_login;
            $stmt->bind_param('ssssi', $firstname, $lastname, $referrer, $userLogin, $bookingId);
        
            if(false === $stmt->execute()) {
                throw new DatabaseException("Error during UPDATE: " . $mysqli->error);
            }
            $stmt->close();

            foreach ($auditMsgs as $msg) {
                self::insertBookingComment($mysqli, new BookingComment($bookingId, $msg, BookingComment::COMMENT_TYPE_AUDIT));
            }
        }
    }    

    /**
     * Inserts a new booking comment.
     * $mysqli : manual db connection (for transaction handling)
     * $bookingComment : comment to insert  (immutable)
     * Returns id of inserted booking id
     * Throws DatabaseException on insert error
     */
    static function insertBookingComment($mysqli, $bookingComment) {
    
error_log("insertBookingComment $bookingComment->bookingId  $bookingComment->comment  $bookingComment->commentType");
        global $wpdb;
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."bookingcomment(booking_id, comment, comment_type, created_by, created_date)
             VALUES(?, ?, ?, ?, ?)");
        $stmt->bind_param('sssss', 
            $bookingComment->bookingId, 
            $bookingComment->comment, 
            $bookingComment->commentType, 
            $bookingComment->createdBy, 
            $bookingComment->createdDate->format('Y-m-d H:i:s'));
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during INSERT: " . $mysqli->error);
        }
        $stmt->close();

        return $mysqli->insert_id;
    }
    
    /**
     * Loads all comments matching the given booking id.
     * $bookingId : id of existing booking id
     * $commentType : type of comment to retrieve (optional; returns all if not specified)
     * Returns array() of BookingComment
     * Throws DatabaseException on error
     */
    static function fetchBookingComments($bookingId, $commentType = null) {
        global $wpdb;
        $return_val = array();
        
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT comment_id, booking_id, comment, comment_type, created_by, DATE_FORMAT(created_date, '%%Y-%%m-%%d %%H:%%i:%%s') AS created_date
               FROM ".$wpdb->prefix."bookingcomment
              WHERE booking_id = %d
                AND ". ($commentType == null ? "'_ALL_' = %s" : "comment_type = %s")."
              ORDER BY comment_id", $bookingId, $commentType == null ? "_ALL_" : $commentType));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        foreach ($resultset as $res) {
            $comment = new BookingComment($res->booking_id, $res->comment, $res->comment_type, 
                $res->created_by, DateTime::createFromFormat('Y-m-d H:i:s', $res->created_date));
            $comment->id = $res->comment_id;
            $return_val[] = $comment;
        }
        return $return_val;
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
//debuge($sql, $sqlparams);
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
            $result[$res->booking_id]->comments = 
                self::fetchBookingComments($res->booking_id, BookingComment::COMMENT_TYPE_USER);
            $result[$res->booking_id]->bookingDates = 
                AllocationDBO::fetchDatesForBookingId($res->booking_id);
        }
//debuge($result);
        return $result;
    }
    
}

?>