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
     */
    static function getAllResources() {
        global $wpdb;

        // query all our resources (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT resource_id, name, capacity, lvl, path, number_children 
               FROM ".$wpdb->prefix."v_resources_by_path
              ORDER BY path"));
        
        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res;
        }
        return $result;
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
    
    /**
     * Returns the selection list of parent resources when adding a new resource.
     * The returned collection will have the following properties:
     * resource_id : id of allowable parent resource 
     * name : name of allowable parent resource
     */
     // DEPRECATED -- should be able to select any parent
    static function getParentResourceSelection() {
        global $wpdb;
        // only one level of descendants are allowed
        $parents = $wpdb->get_results($wpdb->prepare(
            "SELECT resource_id, name FROM ".$wpdb->prefix."bookingresources 
              WHERE parent_resource_id IS NULL
              ORDER BY resource_id"));
    }
}

?>