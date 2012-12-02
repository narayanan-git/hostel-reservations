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
     * level : depth of tree (starting at 1)
     * path : tree path by resource id
     * number_children : number of children (0 for leaf nodes)
     * parent_resource_id : resource id of parent (optional)
     * parent_name : name of parent resource (if applicable)
     * resource_type : one of bed, room, group
     * room_type : one of M, F, MX, "" (null)
     */
    static function getAllResources($resourceId = null) {
        global $wpdb;

        // query all our resources (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.resource_id, r.name, r.level, r.path, r.number_children, r.parent_resource_id, rp.name AS parent_name, r.resource_type, r.room_type
               FROM ".$wpdb->prefix."mv_resources_by_path r
               LEFT OUTER JOIN ".$wpdb->prefix."bookingresources rp ON r.parent_resource_id = rp.resource_id
                    ". ($resourceId == null ? "" : "
                            WHERE (r.path LIKE '%%/$resourceId'
                                OR r.path LIKE '%%/$resourceId/%%')") . "
              ORDER BY r.path"));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $result = array();
        foreach ($resultset as $res) {
            $result[$res->resource_id] = $res;
        }
        return $result;
    }
    
    /**
     * Fetches resource recordset by resource_id. 
     * $resourceId : id of resource 
     * Returns resultset with the following properties:
     * resource_id : id of resource
     * name : resource name
     * capacity : capacity of resource
     * level : depth of tree (starting at 1)
     * path : tree path by resource id
     * number_children : number of children (0 for leaf nodes)
     * parent_resource_id : resource id of parent (optional)
     * parent_name : name of parent resource (if applicable)
     * resource_type : one of bed, room, group
     * room_type : one of M, F, MX, "" (null)
     * Throws DatabaseException if resourceId does not exist
     */
    static function fetchResourceById($resourceId) {
        global $wpdb;

        // query resource by id
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.resource_id, r.name, r.level, r.path, r.number_children, r.parent_resource_id, r.resource_type, r.room_type
               FROM ".$wpdb->prefix."mv_resources_by_path r
              WHERE r.resource_id = %d
              ORDER BY r.path", $resourceId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        foreach ($resultset as $res) {
            return $res;
        }
        throw new DatabaseException("Resource $resourceId not found");
    }
    
    /**
     * Returns all properties for a given resource_id. 
     * Each object in the collection returned has the following properties:
     * $resourceId : id of resource (optional; if not specified, returns all properties)
     * Returns resultset array() indexed by property id
     * property_id : id of resource property
     * description : description of property
     * selected_yn : 'Y' if property is selected for resource, 'N' otherwise
     */
    static function getPropertiesForResource($resourceId = 0) {
        global $wpdb;

        // query all our resources and properties (in order)
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT rp.property_id, rp.description, CASE WHEN rpm.property_id IS NULL THEN 'N' ELSE 'Y' END AS selected_yn 
               FROM ".$wpdb->prefix."resource_properties rp
               LEFT OUTER JOIN ".$wpdb->prefix."resource_properties_map rpm ON rp.property_id = rpm.property_id AND rpm.resource_id = %d
              ORDER BY rp.property_id", $resourceId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $result = array();
        foreach ($resultset as $res) {
            $result[$res->property_id] = $res;
        }
        return $result;
    }
    
    /**
     * Update the properties for a particular resource to the DB.
     * $resourceId : id of resource to update
     * $propertyArray : array() of property ids applicable for this resource
     */
    static function updateResourceProperties($resourceId, $propertyArray) {
error_log("updateResourceProperties $resourceId , ".var_export($propertyArray, true));

        global $wpdb;
        $dblink = new DbTransaction();
        try {
            $stmt = $dblink->mysqli->prepare(
                    "DELETE FROM ".$wpdb->prefix ."resource_properties_map
                      WHERE resource_id = ?");
            $stmt->bind_param('i', $resourceId);
            if(false === $stmt->execute()) {
                throw new DatabaseException("Error occurred deleting from resource_properties_map :".$dblink->mysqli->error);
            }
            $stmt->close();

            // iterate and insert
            $stmt = $dblink->mysqli->prepare(
                    "INSERT INTO ".$wpdb->prefix ."resource_properties_map (property_id, resource_id)
                     VALUES(?, ?)");
            foreach ($propertyArray as $propertyId) {
                $stmt->bind_param('ii', $propertyId, $resourceId);
                if(false === $stmt->execute()) {
                    throw new DatabaseException("Error occurred inserting into resource_properties_map :".$dblink->mysqli->error);
                }
            }
            $stmt->close();

        } catch(Exception $ex) {
            $dblink->mysqli->rollback();
            $dblink->mysqli->close();
            throw $e;
        }

        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }
    
    /**
     * Returns all resources mapped by ID.
     * Returns (array of) id -> resource name
     */
    static function getResourceMap() {
        $result = array();
        foreach (self::getAllResources() as $res) {
            $result[$res->resource_id] = $res->name;
        }
        return $result;
    }
    
    /**
     * Inserts a new resource.
     * $name : name of new resource
     * $capacity : capacity of new resource (optional if parent resource)
     * $parentResourceId : id of parent resource (optional)
     * $resourceType : one of group, room, bed
     * Throws DatabaseException on insert error
     */
    static function insertResource($name, $capacity, $parentResourceId, $resourceType) {
        $dblink = new DbTransaction();

        // easiest way to represent a single unit is to create separate resources 
        // for each individual bed in the room
        try {
            if ($resourceType == 'private' || $resourceType == 'room') {
        
                // first insert the parent record
                $newId = self::insertResourceDb($dblink->mysqli, $name, $parentResourceId, $resourceType);
                
                // insert a record for each 'bed' in the room
                for($i = 0; $i < $capacity; $i++) {
                    self::insertResourceDb($dblink->mysqli, 'Bed-'.($i+1), $newId, 'bed');
                }
            }
            else {
                $newId = self::insertResourceDb($dblink->mysqli, $name, $parentResourceId, $resourceType);
            }

        } catch(Exception $ex) {
            $dblink->mysqli->rollback();
            $dblink->mysqli->close();
            throw $e;
        }
        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }
    
    /**
     * Inserts a new resource to the db within the current transaction.
     * $mysqli : current database connection
     * $name : name of new resource
     * $parentResourceId : id of parent resource (optional)
     * $resourceType : one of group, room, private, bed
     * Returns ID of inserted record
     * Throws DatabaseException on insert error
     */
    static function insertResourceDb($mysqli, $name, $parentResourceId, $resourceType) {
        global $wpdb;
        $stmt = $mysqli->prepare(
                "INSERT INTO ".$wpdb->prefix ."bookingresources (name, parent_resource_id, resource_type, created_by, created_date, last_updated_by, last_updated_date)
                 VALUES(?, ?, ?, ?, NOW(), ?, NOW())");
        $userLogin = wp_get_current_user()->user_login;
        $stmt->bind_param('sisss', $name, $parentResourceId, $resourceType, $userLogin, $userLogin);
        if(false === $stmt->execute()) {
            throw new DatabaseException("Error occurred inserting into resource :".$mysqli->error);
        }
        $newId = $stmt->insert_id;
        $stmt->close();
        return $newId;
    }
    
    /**
     * Deletes a resource by ID.
     * If the resource has child resources, they will also be deleted.
     * $resourceId : id of resource to delete
     * Throws DatabaseException if there are underlying bookings linked to resource
     */
    static function deleteResource($resourceId) {
        global $wpdb;

        $arr_resource_ids = array();   // all resource ids we need to delete
        $parentResourceIds = array();  // parent resource ids for current iteration
        $parentResourceIds[] = $resourceId;
        
        // walk down the tree from $resourceId and collect those which share the same parent
        while(sizeof($parentResourceIds) > 0) {
            
            array_push($arr_resource_ids, $parentResourceIds);
            $resstr = '';
            foreach ($parentResourceIds as $p_res) {
                $resstr .= "$p_res,";
            }
            $resstr = rtrim($resstr, ',');

            $resultset = $wpdb->get_results($wpdb->prepare(
                    "SELECT resource_id 
                       FROM ".$wpdb->prefix."bookingresources
                      WHERE parent_resource_id IN ($resstr)"));
            $parentResourceIds = array();
            foreach ($resultset as $res) {
                $parentResourceIds[] = $res->resource_id;
            }
        }
        
        // then delete from child to parent because of FK constraints
        $dblink = new DbTransaction();
        while( sizeof($arr_resource_ids) > 0) {
        
            $resstr = '';
            foreach (array_pop($arr_resource_ids) as $p_res) {
                $resstr .= "$p_res,";
            }
            $resstr = rtrim($resstr, ',');

            $stmt = $dblink->mysqli->prepare(
                  "DELETE FROM ".$wpdb->prefix ."bookingresources 
                    WHERE resource_id IN ($resstr)");
            if(false === $stmt->execute()) {
                $errormsg = $dblink->mysqli->error;
                $stmt->close();
                $dblink->mysqli->rollback();
                $dblink->mysqli->close();
                if(false === strpos($errormsg, "foreign key constraint fails")) { 
                    throw new DatabaseException("Error occurred deleting resource : $errormsg");
                } else {
                    throw new DatabaseException("Resource cannot be deleted as there are linked bookings");
                }
            }
            $stmt->close();
        }
        $dblink->mysqli->commit();
        $dblink->mysqli->close();
    }

    /**
     * Edits a resource by ID.
     * $resourceId : id of resource to edit
     * $resourceName : new name of resource
     */
    static function editResource($resourceId, $resourceName) {
        global $wpdb;
        $userLogin = wp_get_current_user()->user_login;
        if( false === $wpdb->query($wpdb->prepare(
                "UPDATE ".$wpdb->prefix ."bookingresources
                    SET name = %s,
                        last_updated_by = %s,
                        last_updated_date = NOW()
                  WHERE resource_id = %d", $resourceName, $userLogin, $resourceId))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            throw new DatabaseException("Error occurred updating resource :".$wpdb->last_error);
        }
    }
    
    /**
     * Returns all resources for the given bookingId
     * $bookingId : valid booking id
     * Returns array() of String indexed by resourceId
     */
    function fetchResourcesForBookingId($bookingId) {
        // find all "parent" resources (rooms) for this booking
        global $wpdb;

        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT p.resource_id, p.name
               FROM ".$wpdb->prefix."booking b
               JOIN ".$wpdb->prefix."allocation a ON b.booking_id = a.booking_id
               JOIN ".$wpdb->prefix."bookingresources r ON a.resource_id = r.resource_id
               JOIN ".$wpdb->prefix."bookingresources p ON r.parent_resource_id = p.resource_id
              WHERE p.resource_type = 'room'
                AND b.booking_id = %d
              ORDER BY p.resource_id", $bookingId));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[$res->resource_id] = $res->name;
        }
        return $return_val;
    }
    
    /**
     * Returns DailySummaryResource(s) for the specified date. 
     * (number of checkins, checkouts, etc..)
     * $selectedDate : DateTime object for the current date to query
     * Returns array() of DailySummaryResource for date
     */
    static function fetchDailySummaryResources($selectedDate) {
        // keep track of all resources above 'bed' level
        $return_val = array();
        foreach (self::getAllResources() as $res) {
            if ($res->resource_type != 'bed') {
                $return_val[$res->resource_id] = new DailySummaryResource(
                    $res->resource_id, $res->name, $res->level, $res->path, $res->parent_resource_id);
                    
                // has a parent, parent should already be defined so link child to parent
                if ($res->level > 1) { 
                    $return_val[$res->parent_resource_id]->addChildResource($return_val[$res->resource_id]);
                }
            }
        }

        // now go thru our counts and update our return object array
        // for checkins
        foreach (self::fetchCheckedInCountRecordset($selectedDate) as $rs) {
            $return_val[$rs->resource_id]->checkedInCount = $rs->checkedin_count;
            $return_val[$rs->resource_id]->checkedInRemaining = $rs->checkedin_remain;

            // increment parent counts
            for ($resId = $return_val[$rs->resource_id]->parentId; $resId != ''; $resId = $return_val[$resId]->parentId) {
                $return_val[$resId]->checkedInCount += $rs->checkedin_count;  
                $return_val[$resId]->checkedInRemaining += $rs->checkedin_remain;
            }
        }

        // for checkouts
        foreach (self::fetchCheckedOutCountRecordset($selectedDate) as $rs) {
            $return_val[$rs->resource_id]->checkedOutCount = $rs->checkedout_count;
            $return_val[$rs->resource_id]->checkedOutRemaining = $rs->checkedout_remain;

            // increment parent counts
            for ($resId = $return_val[$rs->resource_id]->parentId; $resId != ''; $resId = $return_val[$resId]->parentId) {
                $return_val[$resId]->checkedOutCount += $rs->checkedout_count;  
                $return_val[$resId]->checkedOutRemaining += $rs->checkedout_remain;
            }
        }

        // we don't actually need a handle on child resources as they will be linked from their parent
        foreach ($return_val as $rv) {
            if ($rv->level > 1) {
                unset($return_val[$rv->id]);
            }
        }
        return $return_val;
    }

    /**
     * Queries and returns the recordset of checkins for the selected date with the following fields:
     *     resource_id : id of (one up from leaf-level) resource
     *     checkedin_count : number of checkins on resource
     *     checkedin_remain : number of checkins remaining on resource
     */
    private static function fetchCheckedInCountRecordset($selectedDate) {
        global $wpdb;

        // check-ins for the day: find all allocations for a given day where no reservation exists the day before
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.parent_resource_id AS resource_id,
                    SUM(IF(d.status = 'reserved', 0, 1)) AS checkedin_count,
                    SUM(IF(d.status = 'reserved', 1, 0)) AS checkedin_remain
               FROM ".$wpdb->prefix."bookingresources r
               LEFT OUTER JOIN ".$wpdb->prefix."allocation a ON r.resource_id = a.resource_id
               LEFT OUTER JOIN ".$wpdb->prefix."bookingdates d ON a.allocation_id = d.allocation_id
              WHERE d.booking_date = STR_TO_DATE(%s, '%%d.%%m.%%Y')
                AND NOT EXISTS (SELECT 1 FROM ".$wpdb->prefix."bookingdates d2
                                 WHERE a.allocation_id = d2.allocation_id
                                   AND d2.booking_date = DATE_SUB(d.booking_date, INTERVAL 1 DAY))
              GROUP BY r.parent_resource_id", 
            $selectedDate->format('d.m.Y')));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        return $resultset;
    }
    
    /**
     * Queries and returns the recordset of checkouts for the selected date with the following fields:
     *     resource_id : id of (one up from leaf-level) resource
     *     checkedout_count : number of checkouts on resource
     *     checkedout_remain : number of checkouts remaining on resource
     */
    private static function fetchCheckedOutCountRecordset($selectedDate) {
        global $wpdb;

        // check-ins for the day: find all allocations for a given day where no reservation exists the day before
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT r.parent_resource_id AS resource_id,
                    SUM(IF(d.status IN ('paid', 'hours', 'free') AND IFNULL(d.checked_out, 'N') = 'Y', 1, 0)) AS checkedout_count,
                    SUM(IF(d.status IN ('paid', 'hours', 'free') AND IFNULL(d.checked_out, 'N') = 'Y', 0, 1)) AS checkedout_remain
               FROM ".$wpdb->prefix."bookingresources r
               LEFT OUTER JOIN ".$wpdb->prefix."allocation a ON r.resource_id = a.resource_id
               LEFT OUTER JOIN ".$wpdb->prefix."bookingdates d ON a.allocation_id = d.allocation_id
              WHERE d.booking_date = STR_TO_DATE(%s, '%%d.%%m.%%Y')
                AND NOT EXISTS (SELECT 1 FROM ".$wpdb->prefix."bookingdates d2
                                 WHERE a.allocation_id = d2.allocation_id
                                   AND d2.booking_date = DATE_ADD(d.booking_date, INTERVAL 1 DAY))
              GROUP BY r.parent_resource_id", 
            $selectedDate->format('d.m.Y')));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }
        return $resultset;
    }
    
    /**
     * Returns array() of resource ids that are due to be paid for the given day (or before).
     * (That is, with an allocation of (R)eserved for given date but (P)aid/(F)ree/(H)ours for any day before).
     * $selectedDate : DateTime corresponding to the date to query (usually today)
     * Returns non-null array() of integer
     */
    static function fetchResourceIdsPastDue($selectedDate) {
        global $wpdb;

        // check-ins for the day: find all allocations for a given day where no reservation exists the day before
        $resultset = $wpdb->get_results($wpdb->prepare(
            "SELECT DISTINCT a.resource_id 
               FROM ".$wpdb->prefix."allocation a
              -- booking date of 'R'eserved for given date 
              WHERE EXISTS (
                SELECT 1 FROM ".$wpdb->prefix."bookingdates d 
                 WHERE a.allocation_id = d.allocation_id
                   AND d.booking_date = STR_TO_DATE(%s, '%%d.%%m.%%Y')
                   AND d.status = 'reserved')
              -- booking date of 'P'/'F'/'H' for some date before 
              AND EXISTS ( 
                SELECT 1 FROM ".$wpdb->prefix."bookingdates d 
                 WHERE a.allocation_id = d.allocation_id
                   AND d.booking_date < STR_TO_DATE(%s, '%%d.%%m.%%Y')
                   AND d.status IN ('paid', 'free', 'hours'))", 
            $selectedDate->format('d.m.Y'), $selectedDate->format('d.m.Y')));
        
        if($wpdb->last_error) {
            throw new DatabaseException($wpdb->last_error);
        }

        $return_val = array();
        foreach ($resultset as $res) {
            $return_val[] = $res->resource_id;
        }
        return $return_val;
    }
}

?>