<?php

/**
 * Database object for resources table.
 */
class ResourceDBO {

    /**
     * Returns all resources indexed by resource_id. 
     * Each object in the collection returned has the following properties:
     * resource_id : id of resource
     * name : resource name
     * capacity : capacity of resource
     * lvl : depth of tree (starting at 1)
     * path : tree path by resource id
     * number_children : number of children (0 for leaf nodes)
     * parent_resource_id : resource id of parent (optional)
     */
    static function getAllResources() {
        global $wpdb;

        // query all our resources (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT resource_id, name, capacity, lvl, path, number_children, parent_resource_id 
               FROM ".$wpdb->prefix."v_resources_by_path
              ORDER BY path"));
        
        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res;
        }
        return $result;
    }
    
    /**
     * Fetches all available resource objects.
     * The result will be a nested tree based on their path.
     * $resourceAllocationMap : map of resource id, array[AllocationRow] to assign
     *                          to each resource (optional)
     * Returns array of BookingResource
     */
    static function getAllResourcesNested($resourceAllocationMap = null) {
        
        // resources are path-ordered
        $return_val = array();
        $return_val_map = array();  // map of all resource id => BookingResource in return_val
        foreach (ResourceDBO::getAllResources() as $res) {
            $br = new BookingResource($res->resource_id, $res->name, $res->capacity, $res->lvl, $res->path, $res->number_children);
            if ($br->level == 1) {  // root element
                $return_val[] = $br;
            } else { // child of root
                // if paths are correct, lastAncestor will always be set
                $return_val_map[$res->parent_resource_id]->addChildResource($br);
            }
            
            // if defined, set the list of allocations for the resource
            if($resourceAllocationMap != null && isset($resourceAllocationMap[$br->resourceId])) {
                $br->setAllocationRows($resourceAllocationMap[$br->resourceId]);
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
     */
    static function insertResource($name, $capacity, $parentResourceId) {
        global $wpdb;
        $wpdb->insert($wpdb->prefix ."bookingresources", 
             array( 'name' => $name, 
                    'capacity' => $capacity, 
                    'parent_resource_id' => $parentResourceId));
        ResourceDBO::cleanUpResources();
    }

    /**
     * Cleans up references within the resources table.
     */
    static function cleanUpResources() {
        global $wpdb;
        // for clarity, set parent resource capacity to NULL if there is at least one child resource
        $wpdb->query($wpdb->prepare(
            "UPDATE ".$wpdb->prefix ."bookingresources br_p
              INNER JOIN ".$wpdb->prefix."v_resources_by_path br_c
                 ON br_c.resource_id = br_p.resource_id
                SET br_p.capacity = NULL 
              WHERE br_c.number_children > 0"));
        
        // https://core.trac.wordpress.org/ticket/15158   null's aren't being set properly
        $wpdb->query($wpdb->prepare(
            "UPDATE ".$wpdb->prefix ."bookingresources 
                SET parent_resource_id = NULL 
              WHERE parent_resource_id = 0"));
    }
    
}

?>