<?php

/**
 * Database object for resources table.
 */
class ResourceDBO {

    /**
     * Returns all resources indexed by resource_id. 
     * Each object in the collection returned has the following properties:
     * $resourceId : id of (parent) resource (if not provided, will return all resources)
     * Returns resultset array()
     * resource_id : id of resource
     * name : resource name
     * capacity : capacity of resource
     * lvl : depth of tree (starting at 1)
     * path : tree path by resource id
     * number_children : number of children (0 for leaf nodes)
     * parent_resource_id : resource id of parent (optional)
     * resource_type : one of bed, room, group
     */
    static function getAllResources($resourceId = null) {
        global $wpdb;

        // query all our resources (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT resource_id, name, capacity, lvl, path, number_children, parent_resource_id, resource_type
               FROM ".$wpdb->prefix."v_resources_by_path
                    ". ($resourceId == null ? "" : "
                            WHERE ((path LIKE '%%/$resourceId' AND number_children = 0)
                                OR (path LIKE '%%/$resourceId/%%' AND number_children = 0))") . "
              ORDER BY path"));
        
        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res;
        }
        return $result;
    }
    
    /**
     * Fetches available resource objects by parent resource id.
     * The result will be a nested tree based on their path.
     * $resourceId : id of (parent) resource (if not provided, will return all resources)
     * $allocationCellMap : 2D map of resource id, date [d.m.Y] => array() of AllocationCell to populate for any matched resource
     * Returns array of BookingResource
     */
    static function getBookingResourcesById($resourceId = null, $allocationCellMap = null) {
        
        // resources are path-ordered
        $return_val = array();
        $return_val_map = array();  // map of all resource id => BookingResource in return_val
        foreach (ResourceDBO::getAllResources($resourceId) as $res) {
            $br = new BookingResource($res->resource_id, $res->name, $res->capacity, $res->lvl, $res->path, $res->number_children, $res->resource_type);
            if ($br->level == 1) {  // root element
                $return_val[] = $br;
            } else { // child of root
                // if paths are correct, parent_resource_id will always be set
                $return_val_map[$res->parent_resource_id]->addChildResource($br);
            }
            
            if($allocationCellMap != null && isset($allocationCellMap[$br->resourceId])) {
                $br->setAllocationCells($allocationCellMap[$br->resourceId]);
            }
            $return_val_map[$res->resource_id] = $br;
        }
        return $return_val;
    }
    
    /**
     * Returns all resources mapped by ID.
     * Returns (array of) id -> resource name
     */
    static function getResourceMap() {
        $result = array();
        foreach (ResourceDBO::getAllResources() as $res) {
            $result[$res->resource_id] = $res->name;
        }
        return $result;
    }
    
    /**
     * Inserts a new resource.
     * $name : name of new resource
     * $capacity : capacity of new resource (optional if parent resource)
     * $parentResourceId : id of parent resource (optional)
     * resourceType : one of group, room, bed
     * Throws DatabaseException on insert error
     */
    static function insertResource($name, $capacity, $parentResourceId, $resourceType) {
        global $wpdb;
        
        // https://core.trac.wordpress.org/ticket/15158   partial hack as null's aren't being set properly
        if( false === $wpdb->query($wpdb->prepare(
                "INSERT INTO ".$wpdb->prefix ."bookingresources (name, capacity, parent_resource_id, resource_type)
                 VALUES (%s, %d, ".($parentResourceId == null ? "NULL" : $parentResourceId).", %s)", $name, $capacity, $resourceType))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException("Error occurred inserting into resource :".$wpdb->last_error);
        }
        
        ResourceDBO::cleanUpResources();
    }

    /**
     * Cleans up references within the resources table.
     */
    static function cleanUpResources() {
        global $wpdb;
        // for clarity, set parent resource capacity to NULL if there is at least one child resource
        if( false === $wpdb->query($wpdb->prepare(
                "UPDATE ".$wpdb->prefix ."bookingresources br_p
                  INNER JOIN ".$wpdb->prefix."v_resources_by_path br_c
                     ON br_c.resource_id = br_p.resource_id
                    SET br_p.capacity = NULL 
                  WHERE br_c.number_children > 0"))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException("Error occurred updating resource :".$wpdb->last_error);
        }
 
        // capacity for bed cannot be anything other than 1
        if( false === $wpdb->query($wpdb->prepare(
                "UPDATE ".$wpdb->prefix ."bookingresources 
                    SET capacity = 1
                  WHERE resource_type = 'bed'"))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException("Error occurred updating resource :".$wpdb->last_error);
        }
    }
    
}

?>