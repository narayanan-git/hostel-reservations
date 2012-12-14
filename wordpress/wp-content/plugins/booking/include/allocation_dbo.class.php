<?php

/**
 * Database object for allocation/booking dates tables.
 */
class AllocationDBO {

    /**
     * Queries availability for the given resourceId and booking dates.
     * Returns a map of available resources (beds) by resourceId which
     * have availability for *ALL* those dates. 
     * $resourceId  : id of resource id to get availability (null for all)
     * $bookingDates : array() of booking dates in format d.m.Y
     * $resourceProps : array of resource property ids (allocate only to resources with these properties)
     *                  if null, properties will not be filtered
     * Returns map of key => $resourceId, value => $capacity
     */
    static function fetchAvailability($resourceId, $bookingDates, $resourceProps) {
        global $wpdb;
error_log("fetch availability resource id : $resourceId booking dates : " . sizeof($bookingDates) . " props " . implode(',', $resourceProps));
        foreach ($bookingDates as $bd) {
            $bookingDatesString .= "STR_TO_DATE('$bd', '%%d.%%m.%%Y'),";
        }
        $bookingDatesString = rtrim($bookingDatesString, ',');

        // this will bring back all beds that have no allocations for any of the dates given
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT p.resource_id, p.capacity as avail_capacity, p2.resource_type AS parent_resource_type 
               FROM ".$wpdb->prefix."mv_resources_by_path p 
               LEFT OUTER JOIN ".$wpdb->prefix."mv_resources_by_path p2 ON p.parent_resource_id = p2.resource_id
              WHERE p.resource_type = 'bed' 
                " . ($resourceId == null ? "" : "AND (p.path LIKE '%%/$resourceId' OR p.path LIKE '%%/$resourceId/%%')") . "
                AND NOT EXISTS(
                        SELECT 1 FROM ".$wpdb->prefix."bookingdates dt 
                          JOIN ".$wpdb->prefix."allocation a ON dt.allocation_id = a.allocation_id
                         WHERE dt.booking_date IN ($bookingDatesString)
                           AND a.resource_id = p.resource_id)
                -- if we are looking at a private room, only include beds where ALL beds are available for the given date(s)
                AND (p2.resource_type <> 'private'    -- shared dorm
                     OR (p2.resource_type = 'private' -- or private room
                            AND NOT EXISTS(
                                SELECT 1 FROM ".$wpdb->prefix."v_resource_availability ra
                                  JOIN wp_bookingresources r2 on r2.resource_id = ra.resource_id
                                 WHERE r2.parent_resource_id = p.parent_resource_id -- resources share the same parent
                                   AND ra.booking_date IN ($bookingDatesString)
                                   AND ra.used_capacity > 0
                         )))
                " . ($resourceProps == null ? "" : "
                AND EXISTS( -- only match those resources with the properties specified
                        SELECT 1 FROM ".$wpdb->prefix."resource_properties_map m
                         WHERE m.resource_id = p.parent_resource_id  -- match against room only
                           AND m.property_id IN (".implode(',', $resourceProps)."))")));

error_log("fetch availability " . $wpdb->last_query);

        if($wpdb->last_error) {
            error_log("Failed to execute query " . $wpdb->last_query);
            throw new DatabaseException($wpdb->last_error);
        }

        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res->avail_capacity;
        }
        return $result;
    }
    
    /**
     * Given a set of resource ids, find a subset of those resourceIds that will fit numGuests
     * exactly (or closest to filling room without exceeding capacity).
     * $resourceIds : set of leaf nodes (beds)
     * $numGuests : number of guests to fit 
     * Returns : array() of resource ids with the same parent from $resourceIds
     *           or empty array if no parent can fit $numGuests
     */
    static function fetchResourcesUnderOneParentResource($resourceIds, $numGuests) {

        // no resources, nothing to assign
        if(sizeof($resourceIds) == 0) {
            return array();
        }

        global $wpdb;
error_log("fetchResourcesUnderOneParentResource ".implode(',', $resourceIds)." and num guests $numGuests");
        // then find all the direct parents for those resources, counting available capacity for those dates
        // if this returns a non-empty result, assign everyone to the first parent resource
        // otherwise if this is empty, then we assign individually using the resources from the first query
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT br.resource_id, br.parent_resource_id, avail_capacity
               FROM (SELECT parent_resource_id, SUM(1) AS avail_capacity 
                       FROM ".$wpdb->prefix."bookingresources
                      WHERE resource_id IN (".implode(',', $resourceIds).") 
                      GROUP BY parent_resource_id
                    ) available_rooms 
               JOIN ".$wpdb->prefix."bookingresources br 
                 ON br.parent_resource_id = available_rooms.parent_resource_id AND br.resource_id IN (".implode(',', $resourceIds).")
              WHERE avail_capacity >= %d
              ORDER BY avail_capacity, br.parent_resource_id, br.resource_id", $numGuests));
        
        if($wpdb->last_error) {
            error_log("Failed to execute query " . $wpdb->last_query);
            throw new DatabaseException($wpdb->last_error);
        }

        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = 1;  // TODO: this is always 1, should we change this?
        }
error_log("fetchResourcesUnderOneParentResource ".$wpdb->last_query);
error_log("fetchResourcesUnderOneParentResource returning ".sizeof($result));
        return $result;
    }

    /**
     * Inserts a new allocation record.
     * $mysqli : database link (to enforce manual transaction handling)
     * $bookingId : id of parent booking record
     * $resourceId : id of resource to assign this allocation
     * $name : name of guest
     * $gender : M/F/X
     * $reqRoomSize : requested room size (e.g. 8, 10, 10+, P, etc..)
     * $reqRoomType : requested room type (M/F/X)
     * Returns unique id of newly created allocation
     */
    static function insertAllocation($mysqli, $bookingId, $resourceId, $name, $gender, $reqRoomSize, $reqRoomType) {
        global $wpdb;
        
        // create the allocation
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."allocation (booking_id, resource_id, guest_name, gender, req_room_size, req_room_type, created_by, created_date, last_updated_by, last_updated_date)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW())");
            
        $userLogin = wp_get_current_user()->user_login;
        $stmt->bind_param('iissssss', $bookingId, $resourceId, $name, $gender, $reqRoomSize, $reqRoomType, $userLogin, $userLogin);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during INSERT: " . $mysqli->error);
        }
        $stmt->close();
        $allocationId = $mysqli->insert_id;
        
        BookingDBO::insertBookingComment($mysqli, new BookingComment($bookingId, "Adding allocation $allocationId ($name)", BookingComment::COMMENT_TYPE_AUDIT));
        
        return $allocationId;
    }
    
    /**
     * Updates an existing allocation record.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of existing allocation record to update
     * $resourceId : id of resource to assign this allocation
     * $name : name of guest
     * $gender : gender (one of 'M', 'F', 'X')
     * $resourceMap : array() of resource recordset indexed by resource id
     */
    static function updateAllocation($mysqli, $allocationId, $resourceId, $name, $gender, $resourceMap) {
        global $wpdb;
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);
        
        $auditMsgs = array();
        if ($name != $allocationRs->guest_name) {
            $auditMsgs[] = "Updating allocation $allocationId ($allocationRs->guest_name) : changing name to $name";
        }
        if ($resourceId != $allocationRs->resource_id) {
            $auditMsgs[] = "Updating allocation $allocationId ($allocationRs->guest_name) : changing resource from ".$resourceMap[$allocationRs->resource_id]->name ." to ".$resourceMap[$resourceId]->name;
        }
        if ($gender != $allocationRs->gender) {
            $auditMsgs[] = "Updating allocation $allocationId ($allocationRs->guest_name) : changing from ". $allocationRs->gender ." to ".$gender;
        }
        
        // update the allocation if any changes exist
        if (sizeof($auditMsgs) > 0) {
            $stmt = $mysqli->prepare(
                "UPDATE ".$wpdb->prefix."allocation 
                    SET resource_id = ?,
                        guest_name = ?, 
                        gender = ?,
                        last_updated_by = ?, 
                        last_updated_date = NOW()
                WHERE allocation_id = ?");
                
            $stmt->bind_param('isssi', $resourceId, $name, $gender, wp_get_current_user()->user_login, $allocationId);
            
            if(false === $stmt->execute()) {
                throw new DatabaseException("Error during UPDATE: " . $mysqli->error);
            }
            $stmt->close();
            
            foreach ($auditMsgs as $msg) {
                BookingDBO::insertBookingComment($mysqli, new BookingComment($allocationRs->booking_id, $msg, BookingComment::COMMENT_TYPE_AUDIT));
            }
        }
    }
    
    /**
     * Deletes the allocations with the specified ids.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : allocation id to delete
     */
    static function deleteAllocation($mysqli, $allocationId) {
        global $wpdb;
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);
        $bookingId = $allocationRs->booking_id;
        $bookingDates = self::fetchBookingDates($allocationId);
        
        $auditMsg = "Deleting allocation $allocationId (".$allocationRs->guest_name.") assigned to ".$allocationRs->resource_name."
                     Booking Dates: ";
        foreach ($bookingDates as $dt => $bd) {
            $auditMsg .= "$dt => $bd->status, ";
        }
        $auditMsg = rtrim($auditMsg, ", ");
        
        // delete the allocation dates 
        $stmt = $mysqli->prepare(
            "DELETE FROM ".$wpdb->prefix."bookingdates
              WHERE allocation_id = ?");
        $stmt->bind_param('i', $allocationId);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during DELETE: " . $mysqli->error);
        }
        $stmt->close();
            
        // delete the allocation 
        $stmt = $mysqli->prepare(
            "DELETE FROM ".$wpdb->prefix."allocation
              WHERE allocation_id = ?");
        $stmt->bind_param('i', $allocationId);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during DELETE: " . $mysqli->error);
        }
        $stmt->close();
            
        BookingDBO::insertBookingComment($mysqli, new BookingComment($bookingId, $auditMsg, BookingComment::COMMENT_TYPE_AUDIT));
    }

    /**
     * Inserts a booking date for the specified allocation and resource
     * only when availability exists.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of parent allocation record
     * $bookingDate : date to add booking (BookingDate)
     * Returns true if the insert complies with current availability, false otherwise.
     */
    static function insertBookingDate($mysqli, $allocationId, $bookingDate) {
        global $wpdb;
error_log(var_export($bookingDate, true));
        $compliesWithAvailability = self::isResourceAvailable($mysqli, $allocationId, $bookingDate->bookingDate);
        
        // insert the record only if availability exists
        if ($compliesWithAvailability) {
            $userLogin = wp_get_current_user()->user_login;
            $checkedOut = $bookingDate->checkedOut ? 'Y' : 'N';
            $stmt = $mysqli->prepare(
                "INSERT INTO ".$wpdb->prefix."bookingdates (allocation_id, booking_date, status, checked_out, created_by, created_date, last_updated_by, last_updated_date) 
                 VALUES (?, STR_TO_DATE(?, '%d.%m.%Y'), ?, ?, ?, NOW(), ?, NOW())");
            $stmt->bind_param('isssss', $allocationId, $bookingDate->bookingDate->format('d.m.Y'), 
                    $bookingDate->status, $checkedOut, $userLogin, $userLogin);
            if(false === $stmt->execute()) {
                throw new DatabaseException("Error during INSERT: " . $mysqli->error);
            }
            $stmt->close();
        }

        return $compliesWithAvailability;
    }

    /**
     * Inserts the booking dates for the given allocation.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of parent allocation record
     * $bookingDates : array() of BookingDate() to be inserted
     * Returns true if the insert complies with current availability, false otherwise.
     */
    static function insertBookingDates($mysqli, $allocationId, $bookingDates) {
        global $wpdb;
        
        if (empty($bookingDates)) {
            return true;  // nothing to do
        }
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);
        $compliesWithAvailability = true;
        $auditMsg = "Adding dates for allocation $allocationId ($allocationRs->guest_name) and ".$allocationRs->resource_name.": ";

        foreach ($bookingDates as $bd) {
            $auditMsg .= $bd->bookingDate->format('d.m.Y') . " => $bd->status, ";
            $compliesWithAvailability &= self::insertBookingDate($mysqli, $allocationId, $bd);
        }
        $auditMsg = rtrim($auditMsg, ', ');
        
        // keep an audit trail...
        BookingDBO::insertBookingComment($mysqli, new BookingComment($allocationRs->booking_id, $auditMsg, BookingComment::COMMENT_TYPE_AUDIT));
        return $compliesWithAvailability;
    }

    /**
     * This will update the bookingdates for the given allocation id.
     * Depending on whether a record already exists, an insert/update/delete will be done on the record.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of parent allocation record
     * $bookingDates : array() of BookingDate indexed by date (d.m.Y) to be saved
     * Returns true if the update complies with current availability, false otherwise.
     */
    static function mergeUpdateBookingDates($mysqli, $allocationId, $bookingDates) {
error_log("mergeUpdateBookingDates $allocationId ");

        // first find the ones currently saved for this allocationId
        $oldBookingDates = self::fetchBookingDates($allocationId);
        $compliesWithAvailability = true;
        
        // diff existing booking dates with the ones we want to save
        // if it exists in the old but not in the new, delete it
error_log(var_export(array($oldBookingDates, $bookingDates), true));
        $datesToRemove = array_diff_key($oldBookingDates, $bookingDates);
        self::deleteBookingDates($mysqli, $allocationId, $datesToRemove);
        
        // if it exists in the new but not in the old, add it
        $datesToAdd = array_diff_key($bookingDates, $oldBookingDates);
        $compliesWithAvailability &= self::insertBookingDates($mysqli, $allocationId, $datesToAdd);
        
        // if it exists in both, update it
        self::updateBookingDates($mysqli, $allocationId, $oldBookingDates, $bookingDates);
        
        // now we need to check that we didn't overbook anywhere!
        $compliesWithAvailability &= self::isAvailabilityViolated($mysqli, $allocationId);
error_log("mergeUpdateBookingDates returning $compliesWithAvailability");
        return $compliesWithAvailability;
    }
    
    /**
     * Deletes the booking dates for the given allocation.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of parent allocation record
     * $bookingDates : array() of BookingDate indexed by date (d.m.Y) to be deleted
     */
    static function deleteBookingDates($mysqli, $allocationId, $bookingDates) {
        global $wpdb;
error_log("deleteBookingDates $allocationId ".var_export($bookingDates, true));
        if (empty($bookingDates)) {
            return;  // nothing to do
        }
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);

        $auditMsg = "Removing dates for allocation $allocationId ($allocationRs->guest_name) and ".$allocationRs->resource_name.": ";
        $bookingDatesString = "";
        foreach ($bookingDates as $bd => $bdObj) {
            $bookingDatesString .= "STR_TO_DATE('$bd', '%d.%m.%Y'),";
            $auditMsg .= "$bd => $bdObj->status, ";
        }
        $bookingDatesString = rtrim($bookingDatesString, ',');
        $auditMsg = rtrim($auditMsg, ', ');
    
        $stmt = $mysqli->prepare(
            "DELETE FROM ".$wpdb->prefix."bookingdates 
              WHERE allocation_id = ?
                AND booking_date IN ($bookingDatesString)");
        $stmt->bind_param('i', $allocationId);
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during DELETE: " . $mysqli->error);
        }
        $stmt->close();
        
        // keep an audit trail...
        BookingDBO::insertBookingComment($mysqli, new BookingComment($allocationRs->booking_id, $auditMsg, BookingComment::COMMENT_TYPE_AUDIT));
    }
    
    /**
     * Updates the booking dates for the given allocation.
     * Only those dates that appear in *both* the old and new arrays *and* are different will be updated.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of parent allocation record
     * $oldBookingDates : current array() of BookingDate indexed by date (d.m.Y)
     * $newBookingDates : new updated array() of BookingDate indexed by date (d.m.Y)
     */
    static function updateBookingDates($mysqli, $allocationId, $oldBookingDates, $newBookingDates) {
        global $wpdb;
error_log("updateBookingDates $allocationId ");
        
        // these are the dates that exist in both old and new
        $bookingDates = array_intersect_key($oldBookingDates, $newBookingDates);
error_log("updateBookingDates intersection ".var_export($bookingDates, true));
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);

        $stmt = $mysqli->prepare(
            "UPDATE ".$wpdb->prefix."bookingdates 
                SET status = ?,
                    checked_out = ?,
                    last_updated_by = ?,
                    last_updated_date = NOW()
              WHERE allocation_id = ?
                AND booking_date = STR_TO_DATE(?, '%d.%m.%Y')");
        $userLogin = wp_get_current_user()->user_login;
        
        $auditMsg = "";
        foreach ($bookingDates as $bd => $bdObj) {
            // only apply where the status has changed 
            $hasChanged = false;
            if ($oldBookingDates[$bd]->status !== $newBookingDates[$bd]->status) {
                $auditMsg .= "$bd => ".$newBookingDates[$bd]->status.", ";
                $hasChanged = true;
            }
            if ($oldBookingDates[$bd]->checkedOut !== $newBookingDates[$bd]->checkedOut) {
                $hasChanged = true;
            }
                
            // do db update using the same statement
            if ($hasChanged) {
                $checkedOut = $newBookingDates[$bd]->checkedOut ? 'Y' : 'N';
                $stmt->bind_param('sssis', $newBookingDates[$bd]->status, 
                    $checkedOut, $userLogin, $allocationId, $bd);
                if(false === $stmt->execute()) {
                    throw new DatabaseException("Error during UPDATE: " . $mysqli->error);
                }
            }
        }
        $auditMsg = rtrim($auditMsg, ', ');

        $stmt->close();

        // if blank, we didn't actually do anything
        if ($auditMsg !== '') {
            // keep an audit trail...
            $auditMsg = "Updating dates for allocation $allocationId ($allocationRs->guest_name) and ".$allocationRs->resource_name.": " . $auditMsg;
            BookingDBO::insertBookingComment($mysqli, new BookingComment($allocationRs->booking_id, $auditMsg, BookingComment::COMMENT_TYPE_AUDIT));
        }
    }
    
    /**
     * Checks whether the current allocation violates the current availability rules.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of allocation record
     * Returns true if the insert complies with current availability, false otherwise.
     */
    static function isAvailabilityViolated($mysqli, $allocationId) {
        global $wpdb;

        // check that the allocation does not break availability rules
        $stmt = $mysqli->prepare(
            "SELECT MIN(avail_capacity)
               FROM ".$wpdb->prefix."v_resource_availability ra
              WHERE ra.booking_date IN (SELECT booking_date from ".$wpdb->prefix."bookingdates 
                                        WHERE allocation_id = ?)
                AND ra.resource_id IN (SELECT resource_id FROM ".$wpdb->prefix."allocation 
                                        WHERE allocation_id = ?)");
        $stmt->bind_param('ii', $allocationId, $allocationId);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during SELECT: " . $mysqli->error);
        }
        
        $stmt->bind_result($availCapacity);
        $compliesWithAvailability = (! $stmt->fetch()) || $availCapacity >= 0;
error_log("isAvailabilityViolated: availCapacity $availCapacity");
        $stmt->close();
error_log("allocation $allocationId on $bookingDate complies with availability: ". ($compliesWithAvailability ? 'true' : 'false'));
        return $compliesWithAvailability;
    }
    
    /**
     * Checks whether there is current availability for the given allocation and booking date.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of allocation record
     * $bookingDate : date of booking (DateTime)
     * Returns true if availability exists, false otherwise.
     */
    static function isResourceAvailable($mysqli, $allocationId, $bookingDate) {
        global $wpdb;

        // check that the record does not break availability rules
        $stmt = $mysqli->prepare(
            "SELECT avail_capacity
               FROM ".$wpdb->prefix."v_resource_availability ra
              WHERE ra.booking_date = STR_TO_DATE(?, '%d.%m.%Y')
                AND ra.resource_id = (SELECT resource_id FROM ".$wpdb->prefix."allocation 
                                       WHERE allocation_id = ?)");
        $stmt->bind_param('si', $bookingDate->format('d.m.Y'), $allocationId);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during SELECT: " . $mysqli->error);
        }
        
        $stmt->bind_result($availCapacity);
        $compliesWithAvailability = (! $stmt->fetch()) || $availCapacity > 0;
error_log("isResourceAvailable: availCapacity $availCapacity");
        $stmt->close();
error_log("allocation $allocationId on ".$bookingDate->format('d.m.Y')." complies with availability: ". ($compliesWithAvailability ? 'true' : 'false'));       
        return $compliesWithAvailability;
    }
    
    /**
     * Fetches (filtered) allocations between the given start end dates
     * and optionally matched by resource, status, name.
     * $startDate : include allocations where a booking exists after this date (inclusive)  (DateTime)
     * $endDate : include allocations where a booking exists before this date (inclusive)  (DateTime)
     * $resourceId : only match this resource id (optional)
     * $status : only match this status (optional)
     * $name : match this name against guest name or booking first/last name (* wildcard allowed) (optional)
     * Returns array() of BookingResource including matching allocations
     */
    static function getAllocationsByResourceForDateRange($startDate, $endDate, $resourceId = null, $status = null, $name = null) {
        global $wpdb;

        // include day before and day after so we can see whether a booking continues "off the grid"
        $start = clone $startDate;  
        $start->sub(new DateInterval('P1D'));  // decrement by day
        $end = clone $endDate;
        $end->add(new DateInterval('P1D'));  // increment by day
        
        // fetch all matching allocations; key => resource id, value => array[AllocationRow]
        $nameToMatch = $name == null ? '__ALL__' : '%'.str_replace('*', '%', strtolower($name)).'%';
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT alloc.allocation_id, alloc.guest_name, alloc.gender, alloc.resource_id, bk.booking_id, bk.firstname, bk.lastname
               FROM ".$wpdb->prefix."allocation alloc
               JOIN ".$wpdb->prefix."booking bk ON alloc.booking_id = bk.booking_id
               JOIN ".$wpdb->prefix."mv_resources_by_path res ON alloc.resource_id = res.resource_id
              WHERE EXISTS (SELECT 1 FROM ".$wpdb->prefix."bookingdates d 
                             WHERE alloc.allocation_id = d.allocation_id
                               AND ".($status == null ? "'__ALL__'" : "d.status")." = %s)
                    ". ($resourceId == null ? "" : "
                            AND   ((path LIKE '%%/$resourceId' AND number_children = 0)
                                OR (path LIKE '%%/$resourceId/%%' AND number_children = 0))") . "
                AND (".($name == null ? "'__ALL__' =" : "LOWER(alloc.guest_name) LIKE") ." %s
                        OR ".($name == null ? "'__ALL__' =" : "LOWER(bk.firstname) LIKE") ." %s
                        OR ".($name == null ? "'__ALL__' =" : "LOWER(bk.lastname) LIKE") ." %s
                    ) AND EXISTS (SELECT 1 from ".$wpdb->prefix."bookingdates bd
                             WHERE alloc.allocation_id = bd.allocation_id
                               AND booking_date >= STR_TO_DATE(%s, '%%d.%%m.%%Y') 
                               AND booking_date <= STR_TO_DATE(%s, '%%d.%%m.%%Y'))
              ORDER BY res.path",  // ordered by path so view stays ordered
            // bit of trickery to get this to work with nulls
            $status == null ? '__ALL__' : $status, 
            $nameToMatch, $nameToMatch, $nameToMatch,
            $start->format('d.m.Y'), $end->format('d.m.Y')));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
            
        // best we put this into a 2D map, first by resource id, then date
        // value = row in resultset (above)
        $resourceToBookingDateAllocationMap = array();

        foreach ($resultset as $res) {
            foreach (self::fetchBookingDates($res->allocation_id) as $bookingDate => $bdObj) {
                $resourceToBookingDateAllocationMap[$res->resource_id][$bookingDate] = 
                    new AllocationCell($res->allocation_id, $res->booking_id, $res->guest_name, $res->gender, $bdObj->status, null, $bdObj->checkedOut);
            }
        }
        
//error_log('processed allocations and dates, building resource tree');
        $return_val = self::buildResourceTree($startDate, $endDate, $resourceId, $resourceToBookingDateAllocationMap);
//error_log('END getAllocationsByResourceForDateRange');
        return $return_val;
    }
    
    /**
     * Queries the booking resources matching the given resource id and 
     * populates a tree hierarchy of BookingResources populated with the specified allocations.
     * $startDate : include allocations where a booking exists after this date (inclusive)  (DateTime)
     * $endDate : include allocations where a booking exists before this date (inclusive)  (DateTime)
     * $filteredResourceId : id of resource to match (can be parent resource id)
     * $resourceToBookingDateAllocationMap : 2D map of resource id, booking date [d.m.Y] => AllocationCell
     *                                       to insert into model being returned
     * Returns array() of BookingResource including matching allocations
     */
    private static function buildResourceTree($startDate, $endDate, $filteredResourceId, $resourceToBookingDateAllocationMap) {
        // to make it easier to render the front-end table, we will create table "cells"
        // for all dates from $startDate to $endDate for each resource

        // another 2D map, to store the final allocation "cells"
        $resourceBookingDateMap = array();
        
        $resourceMap = ResourceDBO::getAllResources($filteredResourceId);
        foreach (array_keys($resourceMap) as $resourceId) {
            $start = clone $startDate;  // we will incrementally move $start until $start = $endDate

            while ($start <= $endDate) {
                if(isset($resourceToBookingDateAllocationMap[$resourceId][$start->format('d.m.Y')])) {
                    $allocCell = $resourceToBookingDateAllocationMap[$resourceId][$start->format('d.m.Y')];
                    
                    $allocCell->renderState = self::getRenderStateForAllocation(
                        $resourceToBookingDateAllocationMap, $resourceId, $start);

                    // if we are continuing an existing record, blank out name/gender so we don't display it
                    // reduces the amount of xml we generate as well
                    if ($start != $startDate // display name if we are continuing from off the screen
                            && ($allocCell->renderState == 'rounded_right' || $allocCell->renderState == 'rounded_neither')) {
                        $allocCell->name = '';
                        $allocCell->gender = '';
                    }

                    $resourceBookingDateMap[$resourceId][$start->format('d.m.Y')] = $allocCell;

                } else { // allocation doesn't exist for date, place empty cell
                    $resourceBookingDateMap[$resourceId][$start->format('d.m.Y')] = new AllocationCell();
                }
                $start->add(new DateInterval('P1D'));  // increment by day
            }
        }

        // now get all the resources an bind the allocations above to them
        $bookingResources = self::getBookingResourcesById($resourceMap, $resourceBookingDateMap);
        return $bookingResources;
    }
    
    /**
     * Fetches available resource objects by parent resource id.
     * The result will be a nested tree based on their path.
     * $resourceMap : array() of resource recordset indexed by resource id
     * $allocationCellMap : 2D map of resource id, date [d.m.Y] => array() of AllocationCell to populate for any matched resource
     * Returns array of BookingResource
     */
    private static function getBookingResourcesById($resourceMap, $allocationCellMap) {
        
        // resources are path-ordered
        $return_val = array();
        $return_val_map = array();  // map of all resource id => BookingResource in return_val
        foreach ($resourceMap as $res) {
            $br = new BookingResource($res->resource_id, $res->name, $res->level, $res->path, $res->number_children, $res->resource_type);

            // if parent exists, add child to parent... otherwise set it as root
            if ($res->parent_resource_id != '' && isset($return_val_map[$res->parent_resource_id])) {
                $return_val_map[$res->parent_resource_id]->addChildResource($br);
            } else {
                $return_val[] = $br;
            }
            
            if($allocationCellMap != null && isset($allocationCellMap[$br->resourceId]) && $br->type == 'bed') {
                $br->setAllocationCells($allocationCellMap[$br->resourceId]);
            }
            $return_val_map[$res->resource_id] = $br;
        }
        return $return_val;
    }
    
    /**
     * Since we're trying to display all allocations in a grid, for each contiguous allocation, we will try to
     * display the "ends" of the allocation with rounded corners. This will return either 
     * "rounded_left", "rounded_right", "rounded_both", or "rounded_neither"
     * depending on whether there are allocations on the day before and/or day after the given date.
     * $resourceToBookingDateAllocationMap : 3D map of allocation recordset indexed by resource id followed by date (d.m.Y)
     * $resourceId : id of resource for this allocation
     * $forDate : current date to get state of
     * Returns one of:
     * rounded_left: allocation exists on day after but not day before
     * rounded_right: allocation exists on day before but not day after
     * rounded_both: no allocation exists on day before NOR after
     * rounded_neither: allocation exists on day before AND on day after
     */
    private static function getRenderStateForAllocation($resourceToBookingDateAllocationMap, $resourceId, $forDate) {
        $daybefore = clone $forDate;
        $daybefore->sub(new DateInterval('P1D'));  // decrement by day
        $dayafter = clone $forDate;
        $dayafter->add(new DateInterval('P1D'));  // increment by day
        
        // we need to check if it's the same allocation (should always exist in map)
        if (isset($resourceToBookingDateAllocationMap[$resourceId][$forDate->format('d.m.Y')])) {
            $allocationId = $resourceToBookingDateAllocationMap[$resourceId][$forDate->format('d.m.Y')]->id;
        } else {
            throw new Exception("Invalid state, allocation does not exist! AllocationDBO::getRenderStateForAllocation( $resourceId ,".$forDate->format('d.m.Y'));
        }
        
        if (isset($resourceToBookingDateAllocationMap[$resourceId][$daybefore->format('d.m.Y')])
                && $resourceToBookingDateAllocationMap[$resourceId][$daybefore->format('d.m.Y')]->id == $allocationId) {

            if (isset($resourceToBookingDateAllocationMap[$resourceId][$dayafter->format('d.m.Y')])
                    && $resourceToBookingDateAllocationMap[$resourceId][$dayafter->format('d.m.Y')]->id == $allocationId) {
                return "rounded_neither";
            } else {
                return "rounded_right";
            }
        } else {
            if (isset($resourceToBookingDateAllocationMap[$resourceId][$dayafter->format('d.m.Y')]) 
                    && $resourceToBookingDateAllocationMap[$resourceId][$dayafter->format('d.m.Y')]->id == $allocationId) {
                return "rounded_left";
            } else {
                return "rounded_both";
            }
        }
    }
    
    /**
     * Fetches allocation details given the id.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : existing allocation to query
     * Returns recordset for allocation
     */
    static function fetchAllocationForId($mysqli, $allocationId) {
        global $wpdb;

        $resultset = $mysqli->query(
            "SELECT a.allocation_id, a.booking_id, a.guest_name, a.gender, a.resource_id, r.name as resource_name
               FROM ".$wpdb->prefix."allocation a
               JOIN ".$wpdb->prefix."bookingresources r ON a.resource_id = r.resource_id
              WHERE a.allocation_id = $allocationId");
            
        $return_val = $resultset->fetch_object();
        $resultset->close();
        
        if ($return_val == null) {
            throw new DatabaseException("Allocation not found for $allocationId");
        }
        
        return $return_val;
    }
    
    /**
     * This will fetch all allocations for the given booking.
     * $bookingId : existing booking id
     * $resourceMap : map of resource id => resource recordset; if null, load all resources
     * $loadBookingDates : true to load booking dates, false to leave uninitialised (default true)
     * Returns array() of AllocationRow for booking id indexed by id
     */
    static function fetchAllocationRowsForBookingId($bookingId, $resourceMap = null, $loadBookingDates = true) {
        global $wpdb;
        $resourceMap = $resourceMap == null ? ResourceDBO::getAllResources() : $resourceMap;
        
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT a.allocation_id, a.guest_name, a.gender, a.resource_id, a.req_room_size, a.req_room_type
               FROM ".$wpdb->prefix."allocation a
              WHERE a.booking_id = %d
              ORDER BY a.resource_id, a.guest_name", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        
        $return_val = array();
        foreach ($resultset as $res) {
            $ar = new AllocationRow($res->guest_name, $res->gender, $res->resource_id, $res->req_room_size, $res->req_room_type, $resourceMap);
            $ar->id = $res->allocation_id;
            if ($loadBookingDates) {
                $ar->bookingDates = self::fetchBookingDates($res->allocation_id);
            }
            $return_val[$ar->id] = $ar;
            $ar->rowid = $ar->id;
        }
        return $return_val;
    }
    
    /**
     * Returns a map of booking date (d.m.Y) -> BookingDate() for the given allocation id.
     * $allocationId : existing allocation id
     * Returns array() of BookingDate indexed by booking date (String in format d.m.Y)
     */
    static function fetchBookingDates($allocationId) {
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(booking_date, '%%d.%%m.%%Y') AS booking_date, status, checked_out
               FROM ".$wpdb->prefix."bookingdates
              WHERE allocation_id = %d
              ORDER BY booking_date", $allocationId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[$res->booking_date] = new BookingDate(
                    $allocationId, 
                    DateTime::createFromFormat('!d.m.Y', $res->booking_date, new DateTimeZone('UTC')), 
                    $res->status,
                    $res->checked_out == 'Y' ? true : false);
        }
        return $return_val;
    }

    /**
     * Returns all guest names for the given bookingId
     * $bookingId : valid booking id
     * Returns array() of String
     */
    static function fetchGuestNamesForBookingId($bookingId) {
        // find all allocations for this booking
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT a.guest_name
               FROM ".$wpdb->prefix."booking b
               JOIN ".$wpdb->prefix."allocation a ON b.booking_id = a.booking_id
               WHERE b.booking_id = %d", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[] = $res->guest_name;
        }
        return $return_val;
    }

    /**
     * Returns all statuses for the given bookingId
     * $bookingId : valid booking id
     * Returns array() of String
     */
    static function fetchStatusesForBookingId($bookingId) {
        // find all statuses for this booking
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT d.status
               FROM ".$wpdb->prefix."booking b
               JOIN ".$wpdb->prefix."allocation a ON b.booking_id = a.booking_id
               JOIN ".$wpdb->prefix."bookingdates d ON a.allocation_id = d.allocation_id
              WHERE b.booking_id = %d
              UNION ALL
             SELECT DISTINCT 'checked-out' as status
               FROM ".$wpdb->prefix."booking b
               JOIN ".$wpdb->prefix."allocation a ON b.booking_id = a.booking_id
               JOIN ".$wpdb->prefix."bookingdates d ON a.allocation_id = d.allocation_id
              WHERE b.booking_id = %d
                AND d.checked_out = 'Y'", $bookingId, $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[] = $res->status;
        }
        return $return_val;
    }

    /**
     * Returns all dates for the given bookingId
     * $bookingId : valid booking id
     * Returns array() of DateTime
     */
    static function fetchDatesForBookingId($bookingId) {
        // find all statuses for this booking
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT DATE_FORMAT(d.booking_date, '%%d.%%m.%%Y') AS booking_date
               FROM ".$wpdb->prefix."booking b
               JOIN ".$wpdb->prefix."allocation a ON b.booking_id = a.booking_id
               JOIN ".$wpdb->prefix."bookingdates d ON a.allocation_id = d.allocation_id
              WHERE b.booking_id = %d
              ORDER BY d.booking_date", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[] = DateTime::createFromFormat('!d.m.Y', $res->booking_date, new DateTimeZone('UTC'));
        }
        return $return_val;
    }
    
    /**
     * Toggles the checkout status for the given allocation id and checkout date.
     * This will toggle the status for the contiguous block of dates up to and including the checkout date
     * which are currently set at 'P'aid, 'H'ours or 'F'ree.
     * $allocationId : id of allocation to toggle
     * $checkoutDate : date of checkout (DateTime) (multiple checkout days may exist for any particular allocation)
     */
    static function toggleCheckoutOnBookingDate($allocationId, $checkoutDate) {
        global $wpdb;
        $bookingDates = self::fetchBookingDates($allocationId);
        
        if (isset($bookingDates[$checkoutDate->format('d.m.Y')])) {
        
            $toggledState = $bookingDates[$checkoutDate->format('d.m.Y')]->checkedOut ? 'N' : 'Y';
            
            // collect the contiguous dates on or before the checkoutDate
            $dateStr = "";
            for ($dateRunner = clone $checkoutDate; 
                    isset($bookingDates[$dateRunner->format('d.m.Y')]); 
                    $dateRunner->sub(new DateInterval('P1D'))) {
                $bdObj = $bookingDates[$dateRunner->format('d.m.Y')];
                
                // additional break when status is not a valid paid status
                if($bdObj->status != 'paid' && $bdObj->status != 'hours' && $bdObj->status != 'free')
                    break;

                $dateStr .= 'STR_TO_DATE("'.$dateRunner->format('d.m.Y').'", "%%d.%%m.%%Y"),';
            }
            $dateStr = rtrim($dateStr, ',');
    
            if ($dateStr != '') {
                $userLogin = wp_get_current_user()->user_login;
                if( false === $wpdb->query($wpdb->prepare(
                        "UPDATE ".$wpdb->prefix."bookingdates
                            SET checked_out = %s,
                                last_updated_by = %s,
                                last_updated_date = NOW()
                          WHERE booking_date IN ($dateStr)
                            AND allocation_id = %d", $toggledState, $userLogin, $allocationId))) {
                    error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
                    throw new DatabaseException("Error occurred checking out allocation $allocationId:".$wpdb->last_error);
                } 
            }
        } 
    }

    /**
     * Checks-out all applicable allocations for the given booking id and checkout date.
     * $bookingId : id of booking to check out
     * $forDate : date of checkout (default today)
     * Returns updated BookingSummary for booking id
     */
    function doCheckoutsForBooking($bookingId, $forDate = null) {
        if ($forDate == null) {
            $forDate = new DateTime();
        }

        // find all allocations that can be checked-out for the given date and toggle checkout
        $allocationIds = self::findAllowableCheckoutsForBooking($bookingId, $forDate);
        foreach ($allocationIds as $allocationId) {
            self::toggleCheckoutOnBookingDate($allocationId, $forDate);
        }

        return BookingDBO::fetchBookingSummaryForId($bookingId);
    }

    /**
     * Returns array of allocation ids that can be checked-out for the given booking id 
     * and checkout date.
     * $bookingId : ID of booking
     * $forDate : checkout date (default: today)
     * Returns non-null array of allocation id. 
     */
    static function findAllowableCheckoutsForBooking($bookingId, $forDate = null) {
        global $wpdb;

        if ($forDate == null) {
            $forDate = new DateTime();
        }

        $resultset = $wpdb->get_results($wpdb->prepare(
            // allocation exists on forDate with a non-checked out day
            "SELECT a.allocation_id FROM ".$wpdb->prefix."allocation a
               JOIN ".$wpdb->prefix."bookingdates d ON a.allocation_id = d.allocation_id
              WHERE a.booking_id = %d
                AND IFNULL(d.checked_out, 'N') = 'N'
                AND d.booking_date = STR_TO_DATE(%s, '%%d.%%m.%%Y')
                AND d.status IN ('paid', 'free', 'hours')
                AND NOT EXISTS(SELECT 1 FROM ".$wpdb->prefix."bookingdates d2  -- no allocation for the subsequent day
                                WHERE d2.allocation_id = d.allocation_id
                                  AND d2.booking_date = DATE_ADD(d.booking_date, INTERVAL 1 DAY))", 
            $bookingId, $forDate->format('d.m.Y')));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[] = $res->allocation_id;
        }
        return $return_val;
    }

    /**
     * Returns true if and only if there is at least one guest eligible to check out
     * for the current date.
     * $bookingId : ID of booking
     * $forDate : date to check for (default: today)
     * Returns boolean. 
     */
    static function isCheckoutAllowedForBookingId($bookingId, $forDate = null) {
        // checkout allowed if returned allocation array is not empty
        $allocationIds = self::findAllowableCheckoutsForBooking($bookingId, $forDate);
        return false === empty($allocationIds);
    }
}

?>