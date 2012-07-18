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
            "SELECT resource_id, capacity 
               FROM ".$wpdb->prefix."v_resources_by_path vp
              WHERE ((path LIKE '%%/$resourceId' AND number_children = 0)
                 OR (path LIKE '%%/$resourceId/%%' AND number_children = 0))
             -- each resource must have available capacity for all days!
             -- this count should be 0 (valid for double rooms, quads as well... as they can't be shared)
                 AND 0 = (SELECT COUNT(*)   
                           FROM ".$wpdb->prefix."bookingdates bd
                           JOIN ".$wpdb->prefix."allocation alloc ON bd.allocation_id = alloc.allocation_id
                          WHERE alloc.resource_id = $resourceId -- parent resource id
                            AND bd.booking_date IN ($bookingDatesString))"));

        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res->capacity;
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
error_log("fetchResourcesUnderOneParentResource returning ".sizeof($result));
        return $result;
    }

    /**
     * Inserts a new allocation record.
     * $mysqli : database link (to enforce manual transaction handling)
     * $bookingId : id of parent booking record
     * $resourceId : id of resource to assign this allocation
     * $name : name of guest
     * $status : status of allocation (e.g. checkedin, pending, etc.)
     * $gender : M/F
     * Returns unique id of newly created allocation
     */
    static function insertAllocation($mysqli, $bookingId, $resourceId, $name, $gender) {
        global $wpdb;
        
        // create the allocation
        $stmt = $mysqli->prepare(
            "INSERT INTO ".$wpdb->prefix."allocation (booking_id, resource_id, guest_name, gender, created_by, created_date)
            VALUES (?, ?, ?, ?, ?, NOW())");
            
        $stmt->bind_param('iisss', $bookingId, $resourceId, $name, $gender, wp_get_current_user()->user_login);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during INSERT: " . $mysqli->error);
        }
        $stmt->close();
        return $mysqli->insert_id;
    }
    
    /**
     * Inserts a booking date for the specified allocation and resource
     * only when availability exists.
     * $mysqli : database link (to enforce manual transaction handling)
     * $resourceId : id of resource for allocation (should already be defined for this allocation)
     * $allocationId : id of parent allocation record
     * $bookingDate : date to add booking (format d.m.Y)
     * $status : status at booking date (e.g. paid, reserved, etc..)
     * Returns true if the insert complies with current availability, false otherwise.
     * Note: the record is *always* inserted regardless of whether it breaks the current
     * availability rules or not. A manual rollback is required outwith this method if needed.
     */
    static function insertBookingDate($mysqli, $resourceId, $allocationId, $bookingDate, $status) {
        global $wpdb;
    
        // check that the record does not break availability rules
        $stmt = $mysqli->prepare(
            "SELECT avail_capacity
               FROM ".$wpdb->prefix."v_resource_availability ra
              WHERE ra.booking_date = STR_TO_DATE(?, '%d.%m.%Y')
                AND ra.resource_id = ?");
        $stmt->bind_param('si', $bookingDate, $resourceId);
        
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error during SELECT: " . $mysqli->error);
        }
        
        $stmt->bind_result($availCapacity);
        $compliesWithAvailability = (! $stmt->fetch()) || $availCapacity > 0;
error_log("availCapacity $availCapacity");
        $stmt->close();
error_log("allocation $allocationId on $bookingDate complies with availability: ". ($compliesWithAvailability ? 'true' : 'false'));       

        // insert the record only if availability exists
        if ($compliesWithAvailability) {
            $stmt = $mysqli->prepare(
                "INSERT INTO ".$wpdb->prefix."bookingdates (allocation_id, booking_date, status) 
                VALUES (?, STR_TO_DATE(?, '%d.%m.%Y'), ?)");
            $stmt->bind_param('iss', $allocationId, $bookingDate, $status);
            if(false === $stmt->execute()) {
                throw new DatabaseException("Error during INSERT: " . $mysqli->error);
            }
            $stmt->close();
        }

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
                foreach (AllocationDBO::fetchBookingDatesForAllocation($res->allocation_id) as $bookingDate) {
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
     * Loads the payment/booking dates for the given allocation.
     * $allocationId : allocation id to query
     * Returns array() of booking date (format d.m.Y)
     */
    static function fetchBookingDatesForAllocation($allocationId) {
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT allocation_id, DATE_FORMAT(booking_date, '%%d.%%m.%%Y') AS booking_date
               FROM ".$wpdb->prefix."bookingdates
              WHERE allocation_id = $allocationId
              ORDER BY booking_date"));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[] = $res->booking_date;
        }
        return $return_val;
    }
    
    /**
     * This will fetch all allocations for the given booking and prepopulate the AllocationTable.
     * $bookingId : existing booking id
     * Returns AllocationTable for booking id
     */
    static function fetchAllocationTableForBookingId($bookingId) {
        global $wpdb;
        $return_val = new AllocationTable();
        
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT a.allocation_id, a.guest_name, a.gender, a.resource_id
               FROM ".$wpdb->prefix."allocation a
              WHERE a.booking_id = %d
              ORDER BY a.resource_id, a.guest_name", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        
        if(empty($resultset)) {
            throw new DatabaseException("No allocations found for $bookingId");
        }

        $resourceMap = ResourceDBO::getAllResources();   // avoid hitting db, do this only once
        foreach ($resultset as $res) {
            $ar = new AllocationRow($res->guest_name, $res->gender, $res->resource_id, $resourceMap);
            $ar->bookingDatePayment = AllocationDBO::fetchBookingDatePayments($res->allocation_id);
            $return_val->allocationRows[] = $ar;
        }
        return $return_val;
    }
    
    /**
     * Returns a map of booking date -> payment amount for the given allocation id.
     * $allocationId : existing allocation id
     * Returns array() of payment indexed by booking date (String in format d.m.Y)
     */
    static function fetchBookingDatePayments($allocationId) {
        $return_val = array();
        foreach( AllocationDBO::fetchBookingDatesForAllocation($allocationId) as $bookingDate) {
            $return_val[$bookingDate] = 15;  // TODO: hard-coded payment value of 15
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