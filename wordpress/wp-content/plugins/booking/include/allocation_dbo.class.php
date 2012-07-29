<?php

/**
 * Database object for allocation/booking dates tables.
 */
class AllocationDBO {

    /**
     * Queries availability for the given resourceId and booking dates.
     * Returns a map of available resources (beds) by resourceId which
     * have availability for those dates. 
     * $resourceId  : id of resource id to get availability
     * $bookingDates : array() of booking dates in format d.M.y
     * Returns map of key => $resourceId, value => $capacity
     */
    static function fetchAvailability($resourceId, $bookingDates) {
        global $wpdb;
error_log("fetch availability resource id : $resourceId booking dates : " . sizeof($bookingDates));
        foreach ($bookingDates as $bd) {
            $bookingDatesString .= "STR_TO_DATE('$bd', '%%d.%%m.%%Y'),";
        }
        $bookingDatesString = rtrim($bookingDatesString, ',');
error_log("fetch availability $bookingDatesString");

        // this will give the resources that we can book
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT resource_id, avail_capacity 
               FROM ".$wpdb->prefix."v_resource_availability vp
              WHERE (path LIKE '%%/$resourceId' OR path LIKE '%%/$resourceId/%%')
                AND avail_capacity > 0
                AND (booking_date IS NULL OR booking_date IN ($bookingDatesString))"));

error_log("fetch availability ".$wpdb->last_query);
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
              WHERE avail_capacity >= $numGuests
              ORDER BY avail_capacity, br.parent_resource_id, br.resource_id"));
        
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
     * $gender : M/F
     * Returns unique id of newly created allocation
     */
    static function insertAllocation($mysqli, $bookingId, $resourceId, $name, $gender) {
        global $wpdb;
        
        // create the allocation
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."allocation (booking_id, resource_id, guest_name, gender, created_by, created_date, last_updated_by, last_updated_date)
             VALUES (?, ?, ?, ?, ?, NOW(), ?, NOW())");
            
        $userLogin = wp_get_current_user()->user_login;
        $stmt->bind_param('iissss', $bookingId, $resourceId, $name, $gender, $userLogin, $userLogin);
        
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
     * $resourceMap : array() of resource recordset indexed by resource id
     */
    static function updateAllocation($mysqli, $allocationId, $resourceId, $name, $resourceMap) {
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
        
        // update the allocation if any changes exist
        if (sizeof($auditMsgs) > 0) {
            $stmt = $mysqli->prepare(
                "UPDATE ".$wpdb->prefix."allocation 
                    SET resource_id = ?,
                        guest_name = ?, 
                        last_updated_by = ?, 
                        last_updated_date = NOW()
                WHERE allocation_id = ?");
                
            $stmt->bind_param('issi', $resourceId, $name, wp_get_current_user()->user_login, $allocationId);
            
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
        $bookingDateStatuses = self::fetchBookingDateStatuses($allocationId);
        
        $auditMsg = "Deleting allocation $allocationId (".$allocationRs->guest_name.") assigned to ".$allocationRs->resource_name."
                     Booking Dates: ";
        foreach ($bookingDateStatuses as $dt => $status) {
            $auditMsg .= "$dt => $status, ";
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
     * $bookingDate : date to add booking (format d.m.Y)
     * $status : status at booking date (e.g. paid, reserved, etc..)
     * Returns true if the insert complies with current availability, false otherwise.
     */
    static function insertBookingDate($mysqli, $allocationId, $bookingDate, $status) {
        global $wpdb;
    
        $compliesWithAvailability = self::isResourceAvailable($mysqli, $allocationId, $bookingDate);
        
        // insert the record only if availability exists
        if ($compliesWithAvailability) {
            $userLogin = wp_get_current_user()->user_login;
            $stmt = $mysqli->prepare(
                "INSERT INTO ".$wpdb->prefix."bookingdates (allocation_id, booking_date, status, created_by, created_date, last_updated_by, last_updated_date) 
                 VALUES (?, STR_TO_DATE(?, '%d.%m.%Y'), ?, ?, NOW(), ?, NOW())");
            $stmt->bind_param('issss', $allocationId, $bookingDate, $status, $userLogin, $userLogin);
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
     * $bookingDateStatus : array() of statuses indexed by date (d.m.Y) to be inserted
     * Returns true if the insert complies with current availability, false otherwise.
     */
    static function insertBookingDates($mysqli, $allocationId, $bookingDateStatus) {
        global $wpdb;
error_log("insertBookingDates $allocationId ".var_export($bookingDateStatus, true));
        
        if (empty($bookingDateStatus)) {
            return true;  // nothing to do
        }
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);
        $compliesWithAvailability = true;
        $auditMsg = "Adding dates for allocation $allocationId ($allocationRs->guest_name) and ".$allocationRs->resource_name.": ";

        foreach ($bookingDateStatus as $bd => $status) {
            $auditMsg .= "$bd => $status, ";
            $compliesWithAvailability &= self::insertBookingDate($mysqli, $allocationId, $bd, $status);
        }
        $auditMsg = rtrim($auditMsg, ', ');
        
        // keep an audit trail...
        BookingDBO::insertBookingComment($mysqli, new BookingComment($allocationRs->booking_id, $auditMsg, BookingComment::COMMENT_TYPE_AUDIT));
        return $compliesWithAvailability;
    }

    /**
     * This will update the bookingdate statuses for the given allocation id.
     * Depending on whether a record already exists, an insert/update/delete will be done on the record.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of parent allocation record
     * $bookingDateStatus : array() of statuses indexed by date (d.m.Y) to be saved
     * Returns true if the update complies with current availability, false otherwise.
     */
    static function mergeUpdateBookingDates($mysqli, $allocationId, $bookingDateStatus) {
error_log("mergeUpdateBookingDates $allocationId ");

        // first find the ones currently saved for this allocationId
        $oldBookingDateStatus = self::fetchBookingDateStatuses($allocationId);
        $compliesWithAvailability = true;
        
        // diff existing booking dates with the ones we want to save
        // if it exists in the old but not in the new, delete it
error_log(var_export(array($oldBookingDateStatus, $bookingDateStatus), true));
        $datesToRemove = array_diff_key($oldBookingDateStatus, $bookingDateStatus);
        self::deleteBookingDates($mysqli, $allocationId, $datesToRemove);
        
        // if it exists in the new but not in the old, add it
        $datesToAdd = array_diff_key($bookingDateStatus, $oldBookingDateStatus);
        $compliesWithAvailability &= self::insertBookingDates($mysqli, $allocationId, $datesToAdd);
        
        // if it exists in both, update it
        self::updateBookingDates($mysqli, $allocationId, $oldBookingDateStatus, $bookingDateStatus);
        
        // now we need to check that we didn't overbook anywhere!
        $compliesWithAvailability &= self::isAvailabilityViolated($mysqli, $allocationId);
error_log("mergeUpdateBookingDates returning $compliesWithAvailability");
        return $compliesWithAvailability;
    }
    
    /**
     * Deletes the booking dates for the given allocation.
     * $mysqli : database link (to enforce manual transaction handling)
     * $allocationId : id of parent allocation record
     * $bookingDateStatus : array() of statuses indexed by date (d.m.Y) to be deleted
     */
    static function deleteBookingDates($mysqli, $allocationId, $bookingDateStatus) {
        global $wpdb;
error_log("deleteBookingDates $allocationId ".var_export($bookingDateStatus, true));
        if (empty($bookingDateStatus)) {
            return;  // nothing to do
        }
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);

        $auditMsg = "Removing dates for allocation $allocationId ($allocationRs->guest_name) and ".$allocationRs->resource_name.": ";
        $bookingDatesString = "";
        foreach ($bookingDateStatus as $bd => $status) {
            $bookingDatesString .= "STR_TO_DATE('$bd', '%d.%m.%Y'),";
            $auditMsg .= "$bd => $status, ";
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
     * $oldBookingDateStatus : current array() of statuses indexed by date (d.m.Y)
     * $newBookingDateStatus : new updated array() of statuses indexed by date (d.m.Y)
     */
    static function updateBookingDates($mysqli, $allocationId, $oldBookingDateStatus, $newBookingDateStatus) {
        global $wpdb;
error_log("updateBookingDates $allocationId ");
        
        // these are the dates that exist in both old and new
        $bookingDateStatus = array_intersect_key($oldBookingDateStatus, $newBookingDateStatus);
error_log("updateBookingDates intersection ".var_export($bookingDateStatus, true));
        
        // fetch allocation details
        $allocationRs = self::fetchAllocationForId($mysqli, $allocationId);

        $stmt = $mysqli->prepare(
            "UPDATE ".$wpdb->prefix."bookingdates 
                SET status = ?,
                    last_updated_by = ?,
                    last_updated_date = NOW()
              WHERE allocation_id = ?
                AND booking_date = STR_TO_DATE(?, '%d.%m.%Y')");
        $userLogin = wp_get_current_user()->user_login;
        
        $auditMsg = "";
        $bookingDatesString = "";
        foreach ($bookingDateStatus as $bd => $status) {
            // only apply where the status has changed 
            if ($oldBookingDateStatus[$bd] !== $newBookingDateStatus[$bd]) {
error_log("updateBookingDates $bd changed to ".$newBookingDateStatus[$bd]);
                $auditMsg .= "$bd => $newBookingDateStatus[$bd], ";
                
                // do db update using the same statement
                $stmt->bind_param('ssis', $newBookingDateStatus[$bd], $userLogin, $allocationId, $bd);
                if(false === $stmt->execute()) {
                    throw new DatabaseException("Error during UPDATE: " . $mysqli->error);
                }
            }
        }
        $bookingDatesString = rtrim($bookingDatesString, ',');
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
        $stmt->bind_param('si', $bookingDate, $allocationId);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during SELECT: " . $mysqli->error);
        }
        
        $stmt->bind_result($availCapacity);
        $compliesWithAvailability = (! $stmt->fetch()) || $availCapacity > 0;
error_log("isResourceAvailable: availCapacity $availCapacity");
        $stmt->close();
error_log("allocation $allocationId on $bookingDate complies with availability: ". ($compliesWithAvailability ? 'true' : 'false'));       
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

        // fetch all matching allocations; key => resource id, value => array[AllocationRow]
        $nameToMatch = $name == null ? '__ALL__' : '%'.str_replace('*', '%', strtolower($name)).'%';
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT alloc.allocation_id, alloc.guest_name, alloc.gender, alloc.resource_id, bk.firstname, bk.lastname
               FROM ".$wpdb->prefix."allocation alloc
               JOIN ".$wpdb->prefix."booking bk ON alloc.booking_id = bk.booking_id
               JOIN ".$wpdb->prefix."v_resources_by_path res ON alloc.resource_id = res.resource_id
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
            $startDate->format('d.m.Y'), $endDate->format('d.m.Y')));

        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
            
        // best we put this into a 2D map, first by resource id, then date
        // value = row in resultset (above)
        $resourceToBookingDateAllocationMap = array();

        foreach ($resultset as $res) {
            foreach (AllocationDBO::fetchBookingDateStatuses($res->allocation_id) as $bookingDate => $status) {
                $resourceToBookingDateAllocationMap[$res->resource_id][$bookingDate] = $res;
            }
        }
        
        return AllocationDBO::buildResourceTree($startDate, $endDate, $resourceId, $resourceToBookingDateAllocationMap);
    }
    
    /**
     * Queries the booking resources matching the given resource id and 
     * populates a tree hierarchy of BookingResources populated with the specified allocations.
     * $startDate : include allocations where a booking exists after this date (inclusive)  (DateTime)
     * $endDate : include allocations where a booking exists before this date (inclusive)  (DateTime)
     * $filteredResourceId : id of resource to match (can be parent resource id)
     * $resourceToBookingDateAllocationMap : 2D map of resource id, booking date [d.m.Y] => allocation resultset
     *                                       to insert into model being returned
     * Returns array() of BookingResource including matching allocations
     */
    private static function buildResourceTree($startDate, $endDate, $filteredResourceId, $resourceToBookingDateAllocationMap) {
        // to make it easier to render the front-end table, we will create table "cells"
        // for all dates from $startDate to $endDate for each resource
//debuge("buildResourceTree: ".$startDate->format('d.m.Y')." to ".$endDate->format('d.m.Y')." for $filteredResourceId ",
//                $resourceToBookingDateAllocationMap);
        // another 2D map, to store the final allocation "cells"
        $resourceBookingDateMap = array();
        
        foreach (array_keys(ResourceDBO::getAllResources($filteredResourceId)) as $resourceId) {
            $start = clone $startDate;  // we will incrementally move $start until $start = $endDate
            $lastrec = null;  // previous record one day behind so we can modify the previous date when calculating span

            while ($start <= $endDate) {
                if(isset($resourceToBookingDateAllocationMap[$resourceId][$start->format('d.m.Y')])) {
                    $rs = $resourceToBookingDateAllocationMap[$resourceId][$start->format('d.m.Y')];
                    
                    // it's the continuation of the same record
                    if($lastrec != null && $rs->allocation_id == $lastrec->id) {
                        $lastrec->span += 1;
                        
                    // new record found; create a new cell
                    } else {
                        $lastrec = new AllocationCell($rs->allocation_id, $rs->guest_name, $rs->gender);
                        $resourceBookingDateMap[$resourceId][$start->format('d.m.Y')] = $lastrec;
                    }
                 
                } else { // allocation doesn't exist for date, place empty cell
                    if($lastrec != null && $lastrec->id == 0) {
                        $lastrec->span += 1;
                    } else {
                        $lastrec = new AllocationCell();
                        $resourceBookingDateMap[$resourceId][$start->format('d.m.Y')] = $lastrec;
                    }
                }
                $start->add(new DateInterval('P1D'));  // increment by day
            }
        }
//debuge("resourceBookingDateMap ", $resourceBookingDateMap);
        // now get all the resources an bind the allocations above to them
        $bookingResources = ResourceDBO::getBookingResourcesById($filteredResourceId, $resourceBookingDateMap);
//debuge("bookingresources", $bookingResources);
        return $bookingResources;
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
            "SELECT a.allocation_id, a.guest_name, a.gender, a.resource_id
               FROM ".$wpdb->prefix."allocation a
              WHERE a.booking_id = %d
              ORDER BY a.resource_id, a.guest_name", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        
        $return_val = array();
        foreach ($resultset as $res) {
            $ar = new AllocationRow($res->guest_name, $res->gender, $res->resource_id, $resourceMap);
            $ar->id = $res->allocation_id;
            if ($loadBookingDates) {
                $ar->bookingDateStatus = AllocationDBO::fetchBookingDateStatuses($res->allocation_id);
            }
            $return_val[$ar->id] = $ar;
            $ar->rowid = $ar->id;
        }
        return $return_val;
    }
    
    /**
     * Returns a map of booking date -> status (String) for the given allocation id.
     * $allocationId : existing allocation id
     * Returns array() of statuses (String) indexed by booking date (String in format d.m.Y)
     */
    static function fetchBookingDateStatuses($allocationId) {
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DATE_FORMAT(booking_date, '%%d.%%m.%%Y') AS booking_date, status
               FROM ".$wpdb->prefix."bookingdates
              WHERE allocation_id = %d
              ORDER BY booking_date", $allocationId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[$res->booking_date] = $res->status;
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
               WHERE b.booking_id = %d", $bookingId));
        
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
}

?>