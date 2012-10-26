<?php

/**
 * Strategy for allocating (parent) resources across a set of allocations.
 */
class AllocationStrategy {

    private $resourceMap;   // const map of resources 

    /**
     * Default constructor.
     * $resourceMap : (optional) map of resource id -> resource recordset
     *                if not set, all resources will be fetched from dbo
     */
    function AllocationStrategy($resourceMap = null) {
        $this->resourceMap = $resourceMap == null ? ResourceDBO::getAllResources() : $resourceMap;
    }

    /**
     * This will attempt to set the resource id of each of the allocation rows
     * to a child (leaf) resource if a parent one has been specified and one is available.
     * If possible, if allocations share the same parent resource id, the same "room"
     * will be assigned if available.
     * The isAvailable flag on each AllocationRow will be set to true/false
     * depending on whether there is available at the time this call is executed.
     * 
     * $allocationRows : array() of AllocationRow to update
     * $existingAllocationRows : current array() of (uncommitted) AllocationRow
     * $resourceProps : array of resource property ids (allocate only to resources with these properties)
     */
    function assignResourcesForAllocations($allocationRows, $existingAllocationRows, $resourceProps) {

        // for all allocations
        // if allocation is a "parent" node,
        //     collect all dates of all allocations sharing this "parent"
        //     query all available resources for this parent and the associated booking dates
        //     find a room with available capacity to fit all (or leaving as few empty beds as possible in room)
        //     if not possible, assign one by one
        // else (if already assigned to a "bed")
        //     check that it is still available and set its flag
        
error_log("assignResourcesForAllocations ".sizeof($allocationRows));
        foreach ($allocationRows as $alloc) {
error_log($alloc->resourceId ." has this many children : ".$this->resourceMap[$alloc->resourceId]->number_children);

            // collect all dates for all allocations sharing this resourceId
            // (we should only need to check one as they *should* all be the same but just in case)
            $bookingDates = array();
            $numberOfAllocationsSharingThisParent = $this->collectDatesWithResourceId($allocationRows, $alloc->resourceId, $bookingDates);
error_log("after collectDatesWithResourceId numberOfAllocationsSharingThisParent:$numberOfAllocationsSharingThisParent bookingDates : ".sizeof($bookingDates));

            // check that the user didn't do anything stupid like try to assign two people into the same bed
            if ($numberOfAllocationsSharingThisParent > 1 && $this->resourceMap[$alloc->resourceId]->resource_type == 'bed') {
                throw new AllocationException("You cannot assign more than one person into the same bed!");
            }

            // resourceIds : Map of key = resourceId, value = capacity
            // these are all resources that have availability across ALL $bookingDates
            // TODO: actually, all resources should be beds so capacity = 1 always??
            $resourceIds = $this->fetchAvailableCapacity($alloc->resourceId, $bookingDates, $existingAllocationRows, $resourceProps);
error_log("available capacity:");
foreach ($resourceIds as $k => $v) {
    error_log("resource_id $k => $v");
}
            
            // if "parent" node, we need to assign a specific leaf (bed)
            // first, try to fit everyone into the same room
            if($alloc->resourceId == null || $this->resourceMap[$alloc->resourceId]->number_children > 0) {
                // reservedResourceIds : Map of key = resourceId, value = capacity
                $reservedResourceIds = AllocationDBO::fetchResourcesUnderOneParentResource(array_keys($resourceIds), $numberOfAllocationsSharingThisParent);
error_log("found resource ids ".implode(',', array_keys($reservedResourceIds)));

                // if we can't fit everyone in one room, just assign them in order
                if (sizeof($reservedResourceIds) > 0) {
                    $resourceIds = $reservedResourceIds;
                }
            }
            
            // assign all allocations for this parent and continue
            $this->doAssignAllocations($allocationRows, $alloc->resourceId, $resourceIds);
        }
    }
    
    /**
     * This will calculate the available capacities for all leaf resources (beds)
     * by taking the available capacity system-wide less those already in the current
     * allocation for the current dates and parent resource id.
     * $parentResourceId : resource id of parent to calculate availability for
     * $bookingDates : array() of booking dates in format d.M.y
     * $existingAllocationRows : current array() of (uncommitted) AllocationRow
     * $resourceProps : array of resource property ids (allocate only to resources with these properties)
     * Returns map of key => $resourceId, value => $availableCapacity
     */
    function fetchAvailableCapacity($parentResourceId, $bookingDates, $existingAllocationRows, $resourceProps) {
        // map of resourceId => capacity from db
error_log(var_export(array("begin fetchAvailableCapacity", $parentResourceId, $bookingDates), true));
        $resultMap = AllocationDBO::fetchAvailability($parentResourceId, $bookingDates, $resourceProps);
error_log(var_export($resultMap, true));
        
        // adjust against those currently in this allocation (as these haven't been committed yet)
        foreach ($existingAllocationRows as $existingAllocRow) {
            if (isset($resultMap[$existingAllocRow->resourceId]) && $existingAllocRow->isExistsBookingForAnyDate($bookingDates)) {
                $resultMap[$existingAllocRow->resourceId]--;   // decrement capacity
error_log("$existingAllocRow->resourceId found in existing allocation, decrementing capacity to ".$resultMap[$existingAllocRow->resourceId]);
                // remove if no capacity left
                if($resultMap[$existingAllocRow->resourceId] == 0) {
                    unset($resultMap[$existingAllocRow->resourceId]);
                }
            }
        }
error_log("exit fetchAvailableCapacity ".sizeof($resultMap));
        return $resultMap;
    }
    
    /**
     * Collect all dates for all allocations sharing the specified resourceId.
     * $allocationRows : array() of AllocationRow
     * $resourceId : id of parent resource id to filter on (null for any)
     * $bookingDates : reference to array() to save booking dates with matching $resourceId
     * Returns : number of allocations sharing this parent resource id
     */
    function collectDatesWithResourceId($allocationRows, $resourceId, &$bookingDates) {
        $result = 0;
        foreach ($allocationRows as $alloc) {
            if ($alloc->resourceId === $resourceId || $resourceId == null) {
                $result++;
                $bookingDates = array_merge($bookingDates, $alloc->getBookingDates());
            }
        }
        $bookingDates = array_unique($bookingDates);
error_log("returning bookingDates ".sizeof($bookingDates)." and number allocations $result");
        return $result;
    }
    
    /**
     * Update the resourceIds for the allocationRows provided with 
     * the given resourceIds in order. Sets the isAvailable flag to true/false
     * depending on whether we run out of resource ids or not.
     * $allocationRows : array() of AllocationRow to assign
     * $parentResourceId : resource id to assign child resource id to
     * $resourceIds : resource id => capacity map to assign from
     */
    function doAssignAllocations($allocationRows, $parentResourceId, $resourceIds) {
error_log("doAssignAllocations");
        foreach ($allocationRows as $alloc) {
            if ($alloc->resourceId == $parentResourceId) {
                $alloc->isAvailable = false;
                foreach ($resourceIds as $resourceId => $capacity) {
error_log("assigning ".$alloc->name." to resource $resourceId with capacity $capacity");
                    if($capacity > 0) {
error_log("assigning resource $resourceId to $alloc->name");
                        $resourceIds[$resourceId]--;
                        $alloc->isAvailable = true;
                        $alloc->resourceId = $resourceId;
                        break;
                    }
                }
            }
        }
    }
}

?>