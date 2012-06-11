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
            $bookingDatesString .= "'".str_replace('.', '-', $bd). "',";
        }
        $bookingDatesString = rtrim($bookingDatesString, ',');
error_log("fetch availability $bookingDatesString");

        // this will give the resources that we can book
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT resource_id, capacity 
               FROM ".$wpdb->prefix."v_resources_by_path vp
              WHERE ((path LIKE %s AND number_children = 0)
                 OR (path LIKE %s AND number_children = 0))
             -- each resource must have available capacity for all days!
             -- this count should be 0 (valid for double rooms, quads as well... as they can't be shared)
                AND 0 = (SELECT COUNT(*)   
                           FROM ".$wpdb->prefix."bookingdates bd
                           JOIN ".$wpdb->prefix."allocation alloc ON bd.allocation_id = alloc.allocation_id
                          WHERE alloc.resource_id = $resourceId -- parent resource id
                            AND bd.booking_date IN ($bookingDatesString))",
            "%/$resourceId",
            "%/$resourceId/%"));

error_log("result set ".sizeof($resultset));        
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
     *           or null if no parent can fit $numGuests
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
            "SELECT br.resource_id, br.parent_resource_id, avail_capacity, br.capacity
               FROM (SELECT parent_resource_id, SUM(capacity) AS avail_capacity 
                       FROM ".$wpdb->prefix."bookingresources
                      WHERE resource_id IN (".implode(',', $resourceIds).") 
                      GROUP BY parent_resource_id
                    ) available_rooms 
               JOIN ".$wpdb->prefix."bookingresources br 
                 ON br.parent_resource_id = available_rooms.parent_resource_id AND br.resource_id IN (".implode(',', $resourceIds).")
              WHERE avail_capacity >= $numGuests
              ORDER BY avail_capacity, br.resource_id"));
        
        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res->capacity;
        }
error_log("fetchResourcesUnderOneParentResource returning ".sizeof($result));
        return $result;
    }
}

?>