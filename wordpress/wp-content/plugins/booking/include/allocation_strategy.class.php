<?php

/**
 * Strategy for allocating (parent) resources across a set of allocations.
 */
class AllocationStrategy {

    private $resourceMap;   // const map of resources (resourceId -> resource recordset)

    /**
     * Default constructor.
     * $resourceMap : (optional) map of resource id -> resource recordset
     *                if not set, all resources will be fetched from dbo
     */
    function AllocationStrategy($resourceMap = null) {
        $this->resourceMap = $resourceMap == null ? ResourceDBO::getAllResources() : $resourceMap;
    }

    /**
     * Creates new allocation rows and assigns any parent level resource (group/room) to a leaf-level resource (bed)
     * based on current availability and existing allocations.
     * $bookingName : name of booking (allocations will be numbered from the number of existing allocations)
     * $numGuests : array() of gender (M/F/X) => number of guests (int) to allocate for. If possible, the
     *              guests will be placed in the same room.
     * $resourceId : the resource to assign the visitors to. It could be a parent resource (room) 
     *               or a leaf level resource (bed)
     * $reqRoomSize : requested room size (e.g. 8, 10, 10+, P, etc..)
     * $reqRoomType : requested room type (one of M/F/X)
     * $dates : array of dates (String) in format dd.MM.yyyy
     * $resourceProps : array of resource property ids (allocate only to resources with these properties)
     * $existingAllocationRows : current array() of (uncommitted) AllocationRow
     * Returns array() of AllocationRow
     * Throws AllocationException where availability doesn't exist.
     */
    function addAllocation($bookingName, $numGuests, $resourceId, $reqRoomSize, $reqRoomType, $dates, $resourceProps, &$existingAllocationRows) {
        $newAllocations = array();
        $this->insertNewAllocationRows($bookingName, $numGuests['M'], 'M', $resourceId, $reqRoomSize, $reqRoomType, $dates, $existingAllocationRows, $newAllocations);
        $this->insertNewAllocationRows($bookingName, $numGuests['F'], 'F', $resourceId, $reqRoomSize, $reqRoomType, $dates, $existingAllocationRows, $newAllocations);
        $this->insertNewAllocationRows($bookingName, $numGuests['X'], 'X', $resourceId, $reqRoomSize, $reqRoomType, $dates, $existingAllocationRows, $newAllocations);
    /*
    }

     **
     * This will attempt to set the resource id of each of the allocation rows
     * to a child (leaf) resource if a parent one has been specified and one is available.
     * If possible, if allocations share the same parent resource id, the same "room"
     * will be assigned if available.
     * The isAvailable flag on each AllocationRow will be set to true/false
     * depending on whether there is availability at the time this call is executed.
     * 
     * $allocationRows : array() of AllocationRow to update
     * $existingAllocationRows : current array() of (uncommitted) AllocationRow
     * $resourceProps : array of resource property ids (allocate only to resources with these properties)
     *
    function assignResourcesForAllocations($allocationRows, $existingAllocationRows, $resourceProps) {
     */

        // find all allocations which are "parent" nodes
        // collect all unique dates AND male/female requirements for the nodes e.g. 5M; 2M/3F
        //     go through $allocationRows; identify $resource_id, number M, number F, number X
        //     query what is available by resource_id, resource props and with room type (M/F/X), 
        //         return array() of resource_ids (rooms) => avail count
        //     subtract existing allocations rows from available count
        //     if room exists where avail count >= number of guests, assign to that room
        //     otherwise, assign by order of resource_id
        // if already assigned to a "bed"
        //     check that it is still available and set its flag

        // for all allocations
        // if allocation is a "parent" node,
        //     collect all dates of all allocations sharing this "parent"
        //     query all available resources for this parent and the associated booking dates
        //     find a room with available capacity to fit all (or leaving as few empty beds as possible in room)
        //     if not possible, assign one by one
        // else (if already assigned to a "bed")
        //     check that it is still available and set its flag
        
error_log("assignResourcesForAllocations ".sizeof($newAllocations));
        foreach ($newAllocations as $alloc) {
error_log($alloc->resourceId ." has this many children : ".$this->resourceMap[$alloc->resourceId]->number_children);

            // if "parent" node, we need to assign a specific leaf (bed)
            if ($alloc->resourceId == null || $this->resourceMap[$alloc->resourceId]->number_children > 0) {

                // if the requested room type is mixed, then we can find available beds for (FX/X) or (MX/X) if
                // the group is all female or all male. otherwise, keep as mixed.
                $allowableRoomTypes = $reqRoomType;
                if( $reqRoomType == 'X' && $numGuests['F'] == 0 && $numGuests['X'] == 0 ) {
                    $allowableRoomTypes = 'MX';
                } else if( $reqRoomType == 'X' && $numGuests['M'] == 0 && $numGuests['X'] == 0 ) {
                    $allowableRoomTypes = 'FX';
                }

                $this->allocateResources($alloc->resourceId, $allowableRoomTypes, $dates, $resourceProps, $existingAllocationRows, $newAllocations);
            }

/*
            // resourceIds : Map of key = resourceId, value = capacity
            // these are all resources that have availability across ALL $bookingDates
            // TODO: actually, all resources should be beds so capacity = 1 always??
            $resourceIds = $this->fetchAvailableCapacity($alloc->resourceId, $alloc->reqRoomType, $bookingDates, $allocationRows, $existingAllocationRows, $resourceProps);
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
*/
        }
        return $newAllocations;
    }

    /**
     * Assigns all allocation rows matching $resourceId with a specific leaf-level resource (bed)
     * based on current availability, requested room type and existing allocations.
     * $resourceId : id of (parent) resource to update
     * $reqRoomType : requested room type (M/F/X/MX/FX)
     * $bookingDates : array() of booking dates in format d.M.y
     * $resourceProps : array of resource property ids (allocate only to resources with these properties)
     * $allocationRows : array() of current AllocationRows
     * $newAllocations : array() of uncommitted allocation rows. resourceIds that are assigned to a parent resource
     *                   will be allocated to a leaf-level resources if possible. Those unable to be allocated
     *                   will have their isAvailable flag set to false.
     */
    function allocateResources($resourceId, $reqRoomType, $bookingDates, $resourceProps, &$allocationRows, &$newAllocations) {

        // collect all dates for all allocations sharing this resourceId
        // (we should only need to check one as they *should* all be the same but just in case)
        $numUnallocated = $this->getNumAllocationsMatchingResource($alloc->resourceId, $reqRoomType, $newAllocations);
error_log("number unallocated: $numUnallocated");
        $bookingDates = $this->collectDatesWithResourceId($alloc->resourceId, $reqRoomType, $newAllocations);
error_log("after collectDatesWithResourceId: ".$numUnallocated." bookingDates : ".sizeof($bookingDates));

        // check that the user didn't do anything stupid like try to assign two people into the same bed
        if ($numUnallocated > 1 && $alloc->resourceId != null && $this->resourceMap[$alloc->resourceId]->resource_type == 'bed') {
            throw new AllocationException("You cannot assign more than one person into the same bed!");
        } 

        // don't include those we've already allocated but not yet committed
        $excludedResourceIds = $this->getLeafResourceIdsWithOverlappingDates($bookingDates, $allocationRows);

        // find all beds that can be assigned for the given requested room type and dates
        $bedResourceIds = AllocationDBO::fetchAvailableBeds(
            $resourceId, $numUnallocated, $reqRoomType, $bookingDates, $excludedResourceIds, $resourceProps);

        $this->allocateBeds($resourceId, $bedResourceIds, $newAllocations);

        // if we didn't have enough availability to allocate based on reqRoomType, try and allocate just based on gender
/* should have been handled above
        if (sizeof($bedResourceIds) < $numUnallocated) {

            $numUnallocated = $this->getNumAllocationsMatchingResource($resourceId, $reqRoomType, $newAllocations);
            if ($reqRoomType == 'X') {
                $bedResourceIds = AllocationDBO::fetchAvailableBeds($resourceId, $numUnallocated, 
                    $this->inferRoomTypes($resourceId, $newAllocations), 
                    $bookingDates, $excludedResourceIds, $this->getResourceIds($allocationRows), $resourceProps);
            } else if ($reqRoomType == 'M') {
                $bedResourceIds = AllocationDBO::fetchAvailableBeds($resourceId, $numUnallocated, 
                    array('M', 'MX'), 
                    $bookingDates, $excludedResourceIds, $this->getResourceIds($allocationRows), $resourceProps);
            } else if ($reqRoomType == 'F') {
                $bedResourceIds = AllocationDBO::fetchAvailableBeds($resourceId, $numUnallocated, 
                    array('F', 'FX'), 
                    $bookingDates, $excludedResourceIds, $this->getResourceIds($allocationRows), $resourceProps);
            }

            $this->allocateBeds($resourceId, $bedResourceIds, $newAllocations);
        }
*/

        // no more availability, ... add to "overflow" group
//        if (sizeof($bedResourceIds) < $numUnallocated) {
//            $this->allocateBeds($resourceId, $allocationRows, array(ResourceDBO::OVERFLOW_RESOURCE_ID));
//        }



/*
        // update availability subtracting those that have been allocated but not committed
        foreach ($existingAllocationRows as $ar) {
            foreach ($rsAvailability as $key => $rowAvail) {
                if ($rowAvail->resource_id == $ar->resourceId) {
                    unset($rsAvailability[$key]);
                }
            }
        }

        // we may have a situation where the user has some uncommitted allocations
        // in a new room, or where previous (uncommitted) allocations may have changed
        // a room from MX to M or from FX to F  
        // (male/mixed to mixed with addition of a F or female/mixed to mixed with addition of a M)
        // so adding new allocations may bring the room into an invalid M/F state
        // ignore this for now; this will be validated again at the db level when saving

        // count the number of available beds per room
        $bedCounts = array();    // array() of bed resource id[] indexed by room resource ids
        $resourceIds = array();  // all available bed resource ids
        foreach ($rsAvailability as $rowAvail) {
            if (unset($bedCounts[$rowAvail->parent_resource_id])) {
                $bedCounts[$rowAvail->parent_resource_id] = array();
            } 
            $bedCounts[$rowAvail->parent_resource_id][] = $rowAvail->resource_id;
            $resourceIds[] = $rowAvail->resource_id;
        }

        // find resource ids where all can fit in the same room
        $roomTypes = AllocationDBO::fetchRoomTypes($resourceId, $bookingDates);
        foreach ($bedCounts as $parentResId => $bedIds) {
            if (sizeof($bedIds) >= $numGuests && 
                    // do not try to stuff a M/F allocation request into a mixed room at this point
                    (false === isset($roomTypes[$parentResId]) || ($roomTypes[$parentResId] != 'MX' && $roomTypes[$parentResId] != 'FX'))) {

                // if we have enough free beds in the room, use these resources
                $resourceIds = $bedIds;
                break;
            }
        }
        */

//$this->inferRoomType($resourceId, $allocationRows)
//        $this->doAssignAllocations($allocationRows, $resourceId, $resourceIds);
    }

    /**
     * Determines the room type based on:
     * 1) The room type requested for the booking
     * 2) The gender of the guests for the booking
     * If the guest requests a same-sex room, then this has precedence.
     * If all the guests (matching the same parent resource) request a mixed room and they are of the same gender, 
     * then they may be put into a same-sex or mixed room if it is available.
     * Returns array of room types:
     * (M, MX) if reqRoomType is M, (F, FX) if reqRoomType is F
     * or (M, MX) if reqRoomType is X and all $allocationRows->gender for $resourceId are also M
     * or (F, FX) if reqRoomType is X and all $allocationRows->gender for $resourceId are also F
     */
    function inferRoomTypes($resourceId, &$allocationRows) {
        $return_val = null;
        foreach ($allocationRows as $ar) {
            if ($ar->resourceId == $resourceId) {
                if ($ar->reqRoomType != 'X') {
                    return array($reqRoomType, "$reqRoomType"."X");
                } 
                else if ($return_val == null) {
                    $return_val = $ar->gender;
                } 
                else if ($return_val != $ar->gender) {
                    $return_val = 'X';
                }
            }
        }

        return $return_val == "X" ? array("X") : array($return_val, $return_val . "X");
    }

    /**
     * This will calculate the available capacities for all leaf resources (beds)
     * by taking the available capacity system-wide less those already in the current
     * allocation for the current dates and parent resource id.
     * $parentResourceId : resource id of parent to calculate availability for
     * $reqRoomType : requested room type (one of M/F/X)
     * $bookingDates : array() of booking dates in format d.M.y
     * $existingAllocationRows : current array() of (uncommitted) AllocationRow
     * $resourceProps : array of resource property ids (allocate only to resources with these properties)
     * Returns map of key => $resourceId, value => $availableCapacity
     *
    function fetchAvailableCapacity($parentResourceId, $reqRoomType, $bookingDates, $existingAllocationRows, $resourceProps) {
        // map of resourceId => capacity from db
error_log(var_export(array("begin fetchAvailableCapacity", $parentResourceId, $bookingDates), true));
        $resultMap = AllocationDBO::fetchAvailability($parentResourceId, $reqRoomType, $bookingDates, $resourceProps);
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
    */
    
    /**
     * Collect all dates for all allocations sharing the specified resourceId and requested room type.
     * $resourceId : id of parent resource id to filter on (null for any)
     * $reqRoomType : requested room type (M/F/X/MX/FX)
     * $allocationRows : array() of AllocationRow
     * $bookingDates : reference to array() to save booking dates with matching $resourceId
     * Returns : array() of unique booking dates (DateTime)
     */
    function collectDatesWithResourceId($resourceId, $reqRoomType, &$allocationRows) {
        $bookingDates = array();
        foreach ($allocationRows as $alloc) {
            if ($alloc->resourceId === $resourceId || $resourceId == null) {

                // find $alloc->reqRoomType could be M/F/X
                // use stripos() to match MX with M or X, FX with F or X
error_log( "stricmp $reqRoomType  with  $alloc->reqRoomType ");
                if (stripos($reqRoomType, $alloc->reqRoomType) !== false) {
                    $bookingDates = array_merge($bookingDates, $alloc->getBookingDates());
                }
            }
        }
        $bookingDates = array_unique($bookingDates);
        return $bookingDates;
    }
    
    /**
     * Filter all allocations sharing the specified resourceId and requested room type.
     * $resourceId : id of parent resource id to filter on (null for any)
     * $reqRoomType : requested room type (M/F/X)
     * $allocationRows : array() of AllocationRow to look through
     * Returns : array() of allocationRow from $allocationRows sharing this parent resource id
     */
    function filterAllocationRowsMatchingResource($resourceId, $reqRoomType, &$allocationRows) {
        $return_val = array();
        foreach ($allocationRows as $alloc) {
            if ($alloc->resourceId === $resourceId || $resourceId == null) {
                if ($alloc->reqRoomType == $reqRoomType) {
                    $return_val[] = $alloc;
                }
            }
        }
        return $return_val;
    }

    /**
     * Returns array of resource ids (beds) with any dates which overlap $bookingDates
     * $bookingDates : dates to verify
     * $allocationRows : check booking dates for each allocation row
     * Returns array of resource id in $allocationRows where they overlap with those in $bookingDates
     */
    function getLeafResourceIdsWithOverlappingDates($bookingDates, &$allocationRows) {
        $return_val = array();
        foreach ($allocationRows as $ar) {
            if ($ar->isExistsBookingForAnyDate($bookingDates)
                    && $this->resourceMap[$ar->resourceId]->resource_type == 'bed') {
                $return_val[] = $ar->resourceId;
            }
        }
        return $return_val;
    }

    /**
     * Convenience method for counting the number of allocations are currently assigned to a given resource.
     * $resourceId : id of parent resource id to filter on (null for any)
     * $reqRoomType : requested room type (M/F/X)
     * $allocationRows : array() of AllocationRow to look through
     * Returns number of items in allocationRows with a matching resourceId
     */
    function getNumAllocationsMatchingResource($resourceId, $reqRoomType, &$allocationRows) {
        return sizeof($this->filterAllocationRowsMatchingResource($resourceId, $reqRoomType, $allocationRows));
    }

    /**
     * Returns all resource ids from a set of allocation rows.
     * $allocationRows : array() of AllocationRow
     * Return array() of resource id
     */
    function getResourceIds(&$allocationRows) {
        $return_val = array();
        foreach ($allocationRows as $alloc) {
            $return_val[] = $alloc->resourceId;
        }
        return $return_val;
    }
    
    /**
     * Update the resourceIds for the allocationRows provided with 
     * the given resourceIds in order. Sets the isAvailable flag to true/false
     * depending on whether we run out of resource ids or not.
     * $parentResourceId : resource id to assign child resource id to
     * $resourceIds : array() of resource ids to assign from
     * $allocationRows : array() of AllocationRow to assign
     */
    function allocateBeds($parentResourceId, $resourceIds, &$allocationRows) {
error_log("allocateBeds");
        foreach ($allocationRows as $alloc) {
            if ($alloc->resourceId == $parentResourceId) {
                $alloc->isAvailable = false;
                foreach ($resourceIds as $key => $resourceId) {
error_log("assigning ".$alloc->name." to resource $resourceId");
error_log("assigning resource $resourceId to $alloc->name");

//                    if ($resourceId != ResourceDBO::RESOURCE_OVERFLOW_ID) {
//                        unset($resourceIds[$key]);
//                    }
                    $alloc->isAvailable = true;
                    $alloc->resourceId = $resourceId;
                    unset($resourceIds[$key]);
                    break;
                }
            }
        }
    }

    /** 
     * Inserts new AllocationRows for the given number of guests for the given gender and dates.
     * $bookingName : name booking is under
     * $numGuests : (scalar) number of guests (allocation rows) to add
     * $gender : one of 'M', 'F', 'X'
     * $resourceId : id of resource to allocate to (null for any)
     * $reqRoomSize : requested room size (e.g. 8, 10, 10+, P, etc..)
     * $reqRoomType : requested room type (M/F/X)
     * $dates : array of dates (String) in format dd.MM.yyyy
     * $allocationRows : array() of existing allocation rows
     * $newAllocations : array() of AllocationRow to append new rows to
     */
    function insertNewAllocationRows($bookingName, $numGuests, $gender, $resourceId, $reqRoomSize, $reqRoomType, $dates, &$allocationRows, &$newAllocations) {
        for($i = 0; $i < $numGuests; $i++) {
            $allocationRow = new AllocationRow(
                    $bookingName.'-'.(sizeof($allocationRows) + sizeof($newAllocations) + 1), 
                    $gender, $resourceId, $reqRoomSize, $reqRoomType, $this->resourceMap);

            foreach ($dates as $dt) {
                $allocationRow->toggleStatusForDate(trim($dt));
            }
            $newAllocations[] = $allocationRow;
        }
    }
    
}

?>