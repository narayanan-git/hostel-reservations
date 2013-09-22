<?php

/**
 * Helper class for allocating room/group level resources into beds.
 */
class AllocationAvailabiity {

    // non-bed level resource we want to allocate for
    private $resourceId;
    // number of female guests
    private $numF;
    // number of male guests
    private $numM;
    // number of guests of unspecified gender
    private $numX;
    // requested room type; obviously if $numM > 0 and $numF > 0, 
    // then $reqRoomType can't be anything but X (mixed)
    private $reqRoomType;
    // booking dates across all guests
    // array() of booking dates in format d.M.y
    private $bookingDates;
    // array of resource property ids (allocate only to resources with these properties)
    private $resourceProps;
    // current uncommitted allocations (array() of AllocationRow)
    private $existingAllocationRows;
    // the allocations we are currently trying to assign beds for (array() of AllocationRow)
    private $newAllocationRows;
    // const map of resources (resourceId -> resource recordset)
    private $resourceMap;   

    // Default constructor
    function AllocationAvailability($resourceId, $numF, $numM, $numX, $reqRoomType, $bookingDates, 
            $resourceProps, &$existingAllocationRows, &$newAllocationRows, $resourceMap = null) {

        $this->resourceId = $resourceId;
        $this->numF = $numF;
        $this->numM = $numM;
        $this->numX = $numX;
        $this->reqRoomType = $reqRoomType;
        $this->bookingDates = $bookingDates;
        $this->resourceProps = $resourceProps;
        $this->existingAllocationRows = $existingAllocationRows;
        $this->newAllocationRows = $newAllocationRows;
        $this->resourceMap = $resourceMap == null ? ResourceDBO::getAllResources() : $resourceMap;
    }

    // Allocate all in $newAllocationRows where resourceId is not a bed
    function doAllocate() {

        // query all (bed-level) resources available with no allocations for 
        // any of the dates given ordered by resource_id
        $availResourceIds = AllocationDBO::fetchAvailableBeds($this->resourceId, 
                $this->reqRoomType, $this->bookingDates, $this->resourceProps);

        // loop thru $existingAllocationRows removing those resources already present AND 
        // where there is *any* overlap in dates
        foreach ($this->existingAllocationRows as $row) {
            if ($row->isExistsBookingForAnyDate($this-bookingDates)) {
                // remove from $availResourceIds if any of the booking dates overlap
                if(($key = array_search($row->resourceId, $availResourceIds)) !== false) {
                    unset($availResourceIds[$key]);
                }
            }
        }

        // of those that remain; find first available where they belong to the same room 
        // and #available in room <= sum($numF, $numM, $numX)
        // this will be sorted first
        $availResourceIds = sortFirstResourcesThatWillFitCurrentAllocation($availResourceIds);

        // now we go through newAllocationRows and assign resourceIds in turn
        foreach ($availResourceIds as $bedResourceId) {
            foreach ($this->newAllocationRows as $row) {
                if ($row->resourceId == $this->resourceId) {
                    $row->resourceId = $bedResourceId;
                    break;
                }
            }
        }
    }


    /**
     * Given a set of free/available (bed) resource id's, returns a sorted array of resource id's
     * where the first entries belong to the first room where this is enough space to fit the current allocation.
     * $availResourceIds : array() of bed resource ids that are currently available for the given dates
     * Returns array() of bed resource ids that will fit the total number of guests currently being allocated
     * or $availResourceIds if there aren't any rooms that will fit everyone
     */
    private function sortFirstResourcesThatWillFitCurrentAllocation($availResourceIds) {
        $parentCount = array();  // array of room resource id -> running count of number of available beds
        $roomResourceId = null;

        // count up the number of available beds in each room
        foreach ($availResourceIds as $resourceId) {
            $parentId = $this->resourceMap[$resourceId]->parent_resource_id;
            if (isset($parentCount[$parentId])) {
                $parentCount[$parentId]++;
            } else {
                $parentCount[$parentId] = 1;
            }

            // break when we've found one
            if ($parentCount[$parentId] >= $this->numM + $this->numF + $this->numX) {
                $roomResourceId = $parentId;
                break;
            }
        }

        // we found a room that will fit everyone
        if ($roomResourceId != null) {
            $return_val = array();
            // add those that match
            foreach ($availResourceIds as $resourceId) {
                if ($roomResourceId == $this->resourceMap[$resourceId]->parent_resource_id) {
                    $return_val[] = $resourceId;
                }
            }
            // and add those that don't
            foreach ($availResourceIds as $resourceId) {
                if ($roomResourceId != $this->resourceMap[$resourceId]->parent_resource_id) {
                    $return_val[] = $resourceId;
                }
            }
            return $return_val;
        }

        // no room will fit everyone, just return what we were given
        return $availResourceIds;
    }
}
?>
