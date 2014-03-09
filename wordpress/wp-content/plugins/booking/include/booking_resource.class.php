<?php

/**
 * Booking Resource data object.
 */
class BookingResource extends XslTransform {

    var $resourceId;
    var $name;
    var $level;
    var $path;
    var $numberChildren;
    var $type;       // resource type, e.g. group, room, bed
    var $roomType;   // applicable only if resource_type = 'room'; one of M/F/null
    var $freebeds;   // array() of free beds (if type is 'group' or 'room') in same order as allocationCells
    var $unpaid = false;  // mark this resource as unpaid (this property is date specific)
    private $childResources;  // array of BookingResource (where this is a parent resource, ie numberChildren > 0)
    private $allocationCells;  // (optional) array of AllocationCell indexed by date [d.m.Y] assigned to this resource (where this is a child node, ie. numberChildren = 0)
    private $derivedRoomTypes; // (optional) array of char indexed by date [d.m.Y] (only applicable where resource_type = 'room')
    
    /**
     * Default constructor.
     */
    function BookingResource( $resourceId, $name, $level, $path, $numberChildren, $type, $roomType = null ) {
        $this->resourceId = $resourceId;
        $this->name = $name;
        $this->level = $level;
        $this->path = $path;
        $this->numberChildren = $numberChildren;
        $this->type = $type;
        $this->roomType = $roomType;
        $this->freebeds = array();
        $this->childResources = array();
        $this->allocationCells = array();
        $this->derivedRoomTypes = array();
    }
    
    /**
     * Adds child resource to this object's list of children.
     * $childResource : BookingResource to add as child
     */
    function addChildResource($childResource) {
        $this->childResources[] = $childResource;
    }
    
    /**
     * Sets the allocation cells assigned for this resource.
     * This is transaction specific; for any particular set of dates
     * the allocation cells may vary.
     * $allocationCells : array of AllocationCell (indexed by date [d.m.Y])
     */
    function setAllocationCells($allocationCells) {
        $this->allocationCells = $allocationCells;
    }

    /**
     * Returns the allocation cells assigned for this resource.
     */
    function getAllocationCells() {
        return $this->allocationCells;
    }

    /**
     * Updates the renderState for each of the allocation cells
     * for the given resource. The allocationCells must be initialized
     * before calling this method.
     */
    function updateRenderStateForAllocations() {

        foreach( $this->allocationCells as $bookingDate => $allocCell ) {
            $dt = DateTime::createFromFormat('!d.m.Y', $bookingDate, new DateTimeZone('UTC'));
            $allocCell->renderState = $this->getRenderStateForAllocation($dt);

            // if we are continuing an existing record, blank out name/gender so we don't display it
            // reduces the amount of xml we generate as well
            if( key($this->allocationCells) !== $bookingDate // not the first cell (display name if we are continuing from off the screen)
                    && ($allocCell->renderState == 'rounded_right' || $allocCell->renderState == 'rounded_neither')) {
                $allocCell->name = '';
                $allocCell->gender = '';
            }
        }
    }

    /**
     * Since we're trying to display all allocations in a grid, for each contiguous allocation, we will try to
     * display the "ends" of the allocation with rounded corners. This will return either 
     * "rounded_left", "rounded_right", "rounded_both", or "rounded_neither"
     * depending on whether there are allocations on the day before and/or day after the given date.
     * $forDate : current date to get state of
     * Returns one of:
     * rounded_left: allocation exists on day after but not day before
     * rounded_right: allocation exists on day before but not day after
     * rounded_both: no allocation exists on day before NOR after
     * rounded_neither: allocation exists on day before AND on day after
     */
    private function getRenderStateForAllocation($forDate) {
        $daybefore = clone $forDate;
        $daybefore->sub(new DateInterval('P1D'));  // decrement by day
        $dayafter = clone $forDate;
        $dayafter->add(new DateInterval('P1D'));  // increment by day

        // check if we share the same allocationId on the day before or on the day after
        $allocationId = $this->getAllocationCell($forDate)->id;

        $allocCellDayBefore = $this->getAllocationCell($resourceId, $daybefore);
        $allocCellDayAfter = $this->getAllocationCell($resourceId, $dayafter);
        
        if ( $allocCellDayBefore != null && $allocCellDayBefore->id == $allocationId) {

            if ( $allocCellDayAfter != null && $allocCellDayAfter->id == $allocationId) {
                return "rounded_neither";
            } else {
                return "rounded_right";
            }

        } else {

            if ( $allocCellDayAfter != null && $allocCellDayAfter->id == $allocationId) {
                return "rounded_left";
            } else {
                return "rounded_both";
            }
        }
    }

    /**
     * Returns the allocation cell for the given resourceId and booking date.
     *
     * $bookingDate : datetime of booking
     * Returns allocation cell (or null if no allocation found)
     */
    function getAllocationCell( $bookingDate ) {
        if( isset( $this->allocationCells[$bookingDate->format('d.m.Y')] )) {
            return $this->allocationCells[$bookingDate->format('d.m.Y')];
        }
        return null;
    }

    /**
     * Sets the derived room types based on current allocations
     * if the resource type for this booking resource = 'room'.
     * Derived room types can be 'M', 'F', or 'X'
     * $roomTypes : array of char (indexed by date [d.m.Y])
     */
    function setDerivedRoomTypes($roomTypes) {
        $this->derivedRoomTypes = $roomTypes;
    }
    
    /**
     * Updates the number of free beds for this resource if it is a 'group' or a 'room'
     * based on the current set of allocationCells.
     * freeBeds will be initialised with the number of free beds in the group/room using a positional
     * index from 0 .. length($allocationCells) - 1
     */
    function updateFreeBeds() {
        // iterate through each allocation in order and tally up the number of free beds
        $this->freebeds = array();  // int array() indexed by position in allocationCells (count from 0)

        if ($this->type == 'group' || $this->type == 'room') {
            foreach ($this->childResources as $child) {
                $child->updateFreeBeds();   // count children
                
                if ($this->type == 'room') {
                    $i = 0;
                    foreach ($child->allocationCells as $cell) {
                        if (false === isset($this->freebeds[$i])) {
                            $this->freebeds[$i] = 0;
                        }
                        if ($cell->id == 0) {
                            $this->freebeds[$i]++;
                        }
                        $i++;
                    }

                } else {
                    // tally up all child counts by appending
                    foreach ($child->freebeds as $key => $value) {
                        if (isset($this->freebeds[$key])) {
                            $this->freebeds[$key] += $value;
                        } else {
                            $this->freebeds[$key] = $value;
                        }
                    }
                }
            }
        }

        if ($this->type == 'private') {
            // TODO: count the number of empty rooms
        }
    }

    /**
     * Goes through each of our child resources and updates the 'unpaid' flag to true
     * for the given resource ids.
     * $resourceIds : array() of resource ids to mark as unpaid
     */
    function markUnpaidResources($resourceIds) {
        if (sizeof($resourceIds) > 0) {
            if (in_array($this->resourceId, $resourceIds)) {
                $this->unpaid = true;
            }
        
            foreach ($this->childResources as $child) {
                $child->markUnpaidResources($resourceIds);
            }
        }
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $resourceRoot = $domtree->createElement('resource');
        $parentElement = $parentElement->appendChild($resourceRoot);

        $parentElement->appendChild($domtree->createElement('id', $this->resourceId));
        $parentElement->appendChild($domtree->createElement('name', $this->name));
        $parentElement->appendChild($domtree->createElement('path', $this->path));
        $parentElement->appendChild($domtree->createElement('level', $this->level));
        $parentElement->appendChild($domtree->createElement('numberChildren', $this->numberChildren));
        
        if ($this->unpaid) {
            $parentElement->appendChild($domtree->createElement('unpaid', 'true'));
        }

        if ($this->numberChildren > 0) {
            $this->updateFreeBeds();   // this will be req'd for the daily summary
            $freeBedRoot = $parentElement->appendChild($domtree->createElement('freebeds'));
            for ($i = 0; $i < count($this->freebeds); $i++) {
                $freeBedRoot->appendChild($domtree->createElement('freebed', $this->freebeds[$i]));
            }
        }
        
        $parentElement->appendChild($domtree->createElement('type', $this->type));
        $parentElement->appendChild($domtree->createElement('roomtype', $this->roomType));
    
        foreach ($this->childResources as $res) {
            $res->addSelfToDocument($domtree, $parentElement);
        }

        $cells = $parentElement->appendChild($domtree->createElement('cells'));
        // assumes allocation cells are ordered by date
        foreach ($this->allocationCells as $alloc) {
            $alloc->addSelfToDocument($domtree, $cells);
        }

        self::addDerivedRoomTypesToDocument($domtree, $parentElement);
    }

    /**
     * Adds $this->derivedRoomTypes variable to the document rooted at $parentElement.
     * $domtree : DOM document root
     * $parentElement : DOM element where this item will be added
     */
    function addDerivedRoomTypesToDocument($domtree, $parentElement) {
        if ($this->type == 'room') {
            $roomTypesElem = $parentElement->appendChild($domtree->createElement('derivedroomtypes'));
            $lastRoomType = null;
            $span = 0;
            foreach ($this->derivedRoomTypes as $dt => $roomType) {

                // this cell is the same as the last
                if ($lastRoomType == $this->derivedRoomTypes[$dt]) {
                    $span++;

                // cell is different from the last, when not the first cell
                } else if ($span > 0) {
                    // different from last, append last element
                    $roomTypeElem = $roomTypesElem->appendChild($lastRoomType == null ? 
                                $domtree->createElement('roomtype') : 
                                $domtree->createElement('roomtype', $lastRoomType));
                    if ($span > 1) {
                        $spanAttr = $domtree->createAttribute('span');
                        $spanAttr->value = $span;
                        $roomTypeElem->appendChild($spanAttr);
                    }
                    $lastRoomType = $roomType;
                    $span = 1;

                // cell is first and not null
                } else if ($span == 0) {
                    $lastRoomType = $roomType;
                    $span = 1;
                }
            }

            // last element, add to document
            $roomTypeElem = $roomTypesElem->appendChild($lastRoomType == null ? 
                        $domtree->createElement('roomtype') : 
                        $domtree->createElement('roomtype', $lastRoomType));
            if ($span > 1) {
                $spanAttr = $domtree->createAttribute('span');
                $spanAttr->value = $span;
                $roomTypeElem->appendChild($spanAttr);
            }
        }
    }
    
    /**
     * Fetches resource in the following format:
     * For a parent resource:
     *     <resource>
     *         <id>1</id>
     *         <name>8-Bed Dorm</name>
     *         <path>/1</path>
     *         <level>1</level>
     *         <numberChildren>2</numberChildren>
     *         <!-- if room type property specified, this has precedence over dervied room types per day below -->
     *         <roomtype/> <!-- overriding room type property if specified against room; could be M/F/X or blank -->
     *         <derivedroomtypes> <!-- derived room types from bookings; one per day if allocationcells defined for children -->
     *             <roomtype span="2">M</roomtype>
     *             <roomtype span="3"/>
     *             <roomtype>X</roomtype>
     *         </derivedroomtypes>
     *         <freebeds>
     *             <freebed>3</freebed>
     *             ...
     *         </freebeds>
     *         <resource>...</resource>
     *         <resource>...</resource>
     *     </resource>
     *
     * For a child resource:
     *     <resource>
     *         <id>2</id>
     *         <name>Bed A</name>
     *         <path>/1/2</path>
     *         <level>2</level>
     *         <numberChildren>0</numberChildren>
     *         <unpaid>true</unpaid>
     *         <cells>
     *             <allocationcell> ... </allocationcell>
     *             <allocationcell> ... </allocationcell>
     *         <cells>
     *     </resource>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/resources.xsl';
    }

}

?>