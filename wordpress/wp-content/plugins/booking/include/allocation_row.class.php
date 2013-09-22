<?php

/**
 * Encapsulates one row in the allocation table (one bed for one person)
 * and renders it.
 */
class AllocationRow {
    var $rowid;  // unique id for this row prior to creation
    var $id;     // allocation id
    var $name;
    var $gender;
    var $resourceId;
    var $reqRoomSize;   // requested room size (e.g. 8, 10, 10+, P, etc..) 
    var $reqRoomType;   // requested room type (M/F/X) 
    var $showMinDate;   // minimum date to show on the table (DateTime)
    var $showMaxDate;   // maximum date to show on the table (DateTime)
    var $isAvailable;   // boolean : set this flag to true/false during allocation process (if resource is available or not)
    var $bookingDates = array();  // key = booking date (d.m.Y), value = BookingDate()
    private $resourceMap;  // array of resource_id -> resource recordset
    private $STATUSES = array('reserved', 'paid', 'free', 'hours', 'cancelled'); 
    private $GENDERS = array('M', 'F', 'X');

    /**
     * Default constructor.
     * $name : guest name
     * $gender : guest gender
     * $resourceId : resource to assign to guest
     * $reqRoomSize : requested room size (e.g. 8, 10, 10+, P, etc..)
     * $reqRoomType : requested room type (M/F/X)
     * $status : current status of allocation (default: reserved)
     * $resourceMap : array() of resource id -> resource recordset (if not specified, query db for all resources)
     */
    function AllocationRow($name, $gender, $resourceId, $reqRoomSize, $reqRoomType, $resourceMap = null) {
        $this->rowid = null;
        $this->id = 0;
        $this->name = $name;
        $this->gender = $gender;
        $this->resourceId = $resourceId;
        $this->reqRoomSize = $reqRoomSize;
        $this->reqRoomType = $reqRoomType;
        $this->isAvailable = true;
        if($resourceMap == null) {
            $this->resourceMap = ResourceDBO::getAllResources();
        } else {
            $this->resourceMap = $resourceMap;
        }
    }

    /**
     * Toggles the status for this allocation row.
     * $dt  date string in format dd.MM.yyyy
     */
    function toggleStatusForDate($dt) {
        // new date to be added, toggle status
        if(false === isset($this->bookingDates[$dt])) {
            $this->bookingDates[$dt] = new BookingDate(
                    $this->id, 
                    DateTime::createFromFormat('!d.m.Y', $dt, new DateTimeZone('UTC')), 
                    $this->STATUSES[0]);

        // if we had it reset by cycling, reset to start
        } else if ($this->bookingDates[$dt]->status == '') {
            $this->bookingDates[$dt]->status = $this->STATUSES[0];
           
        // status already set, cycle it
        } else {
            $key = array_search($this->bookingDates[$dt]->status, $this->STATUSES);
            
            // if we are at the end of STATUSES, set the status to null so it's available
            if ($key === sizeof($this->STATUSES) - 1) {
                $this->bookingDates[$dt]->status = null;
            } else {
                $this->bookingDates[$dt]->status = $this->STATUSES[$key + 1];
            }
        }
        return $this->bookingDates[$dt]->status;
    }

    /**
     * Toggles the checkout status for the contiguous block of dates on this allocation row.
     * $dt  date string in format dd.MM.yyyy
     */
    function toggleCheckoutStatusForDate($dt) {
        if (isset($this->bookingDates[$dt])) {
            $this->bookingDates[$dt]->checkedOut = ! $this->bookingDates[$dt]->checkedOut;
            
            // now go back in time and set the same checkout status until we run out of dates
            $dateRunner = DateTime::createFromFormat('!d.m.Y', $dt, new DateTimeZone('UTC'));
            $dateRunner->sub(new DateInterval('P1D'));
            while (isset($this->bookingDates[$dateRunner->format('d.m.Y')])) {
                $this->bookingDates[$dateRunner->format('d.m.Y')]->checkedOut = $this->bookingDates[$dt]->checkedOut;
                $dateRunner->sub(new DateInterval('P1D'));
            }
        }
    }

    /**
     * Toggles the gender for this allocation row.
     */
    function toggleGender() {
        $key = array_search($this->gender, $this->GENDERS);
            
        // toggle the next gender
        $this->gender = $this->GENDERS[($key + 1) % sizeof($this->GENDERS)];
    }

    /**
     * Checks whether a booking exists on a particular date.
     * If no date is specified, checks whether a booking exists on *any* date.
     * $dt  date string in format dd.MM.yyyy  (optional)
     */
    function isExistsBooking($dt = '') {
        if($dt == '') {
            return sizeof($this->bookingDates) > 0;
        }
        return isset($this->bookingDates[$dt]);
    }
    
    /**
     * Checks whether a booking exists on any of the given dates.
     * $bookingDates : array of date string in format dd.MM.yyyy
     * Returns true if booking exists on any of the specified dates, false otherwise.
     */
    function isExistsBookingForAnyDate($bookingDates) {
        foreach ($bookingDates as $dt) {
            if($this->isExistsBooking($dt)) {
                return true;
            }
        }
        return false;
    }
    
    /**
     * Returns the minimum date (DateTime) for this allocation.
     */
    function getMinDate() {
        $result = null;
        foreach ($this->bookingDates as $bd) {
            if($result == null || $bd->bookingDate < $result) {
                $result = clone $bd->bookingDate;
            }
        }
        return $result;
    }
    
    /**
     * Returns the maximum date (DateTime) for this allocation.
     */
    function getMaxDate() {
        $result = null;
        foreach ($this->bookingDates as $bd) {
            if($result == null || $bd->bookingDate > $result) {
                $result = $bd->bookingDate;
            }
        }
        return $result;
    }
    
    /**
     * Returns array of booking dates (string) for this AllocationRow.
     */
    function getBookingDates() {
        return array_keys($this->bookingDates);
    }
    
    /**
     * Returns the number of bookings for this allocation before showMinDate.
     */
    function getNumBookingsBeforeMinDate() {
        $result = 0;
        if($this->showMinDate != null) {
            foreach ($this->bookingDates as $bd) {
                if($bd->bookingDate < $this->showMinDate) {
                    $result++;
                }
            }
        }
        return $result;
    }

    /**
     * Returns the number of bookings for this allocation after showMaxDate.
     */
    function getNumBookingsAfterMaxDate() {
        $result = 0;
        if($this->showMaxDate != null) {
            foreach ($this->bookingDates as $bd) {
                if($bd->bookingDate > $this->showMaxDate) {
                    $result++;
                }
            }
        }
        return $result;
    }

    /**
     * Saves current allocation to the db.
     * $mysqli : manual db connection (for transaction handling)
     * $bookingId : booking id for this allocation
     * Returns allocation id of newly created record
     */
    function save($mysqli, $bookingId) {
    
        global $wpdb;
        
        // create the allocation if it doesn't exist
        if ($this->id == 0) {
            $allocationId = AllocationDBO::insertAllocation(
                $mysqli, $bookingId, $this->resourceId, $this->name, $this->gender, $this->reqRoomSize, $this->reqRoomType);
error_log("inserted allocation $allocationId");

            // then create the booking dates for the allocation
            $this->isAvailable = AllocationDBO::insertBookingDates($mysqli, $allocationId, $this->bookingDates);

        } else { // update the existing allocation
            AllocationDBO::updateAllocation($mysqli, $this->id, $this->resourceId, $this->name, $this->gender, $this->resourceMap);
error_log("updating allocation $this->id");

            // clear out those with blank statuses (these are dates now marked as 'available')
            foreach ($this->bookingDates as $dt => $bookingDate) {
                if ($bookingDate->status == '') {
                    unset($this->bookingDates[$dt]);
                }
            }

            // diff existing booking dates with the ones we want to save
            $this->isAvailable = AllocationDBO::mergeUpdateBookingDates($mysqli, $this->id, $this->bookingDates);
        }

        return $allocationId;
    }

    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $xmlRoot = $domtree->createElement('allocation');
        $xmlRoot = $parentElement->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('rowid', $this->rowid));
        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('name', $this->name));
        $xmlRoot->appendChild($domtree->createElement('gender', $this->gender));
        $xmlRoot->appendChild($domtree->createElement('reqRoomSize', $this->reqRoomSize));
        $xmlRoot->appendChild($domtree->createElement('reqRoomType', $this->reqRoomType));
        if ($this->resourceId != null) {
            $xmlRoot->appendChild($domtree->createElement('resourceid', $this->resourceId));
            $xmlRoot->appendChild($domtree->createElement('resource', $this->resourceMap[$this->resourceId]->name));
            $xmlRoot->appendChild($domtree->createElement('parentresource', $this->resourceMap[$this->resourceId]->parent_name));
        }

        $dateRow = $domtree->createElement('dates');
        $xmlRoot->appendChild($dateRow);
        
        // initialise min/max dates if not set
        if($this->showMinDate == null) {
            $this->showMinDate = $this->getMinDate();
        }
        if($this->showMaxDate == null) {
            $this->showMaxDate = $this->getMaxDate();
        }
        
        // adding new allocations, we generate entries for all dates within min/max
        $xmlRoot->appendChild($domtree->createElement('bookingsBeforeMinDate', $this->getNumBookingsBeforeMinDate()));
        $xmlRoot->appendChild($domtree->createElement('bookingsAfterMaxDate', $this->getNumBookingsAfterMaxDate()));
        $xmlRoot->appendChild($domtree->createElement('isAvailable', $this->isAvailable ? 'true' : 'false'));
        
        // loop from showMinDate to showMaxDate creating a date element for every day in between
        // set the appropriate state if a booking exists for that date
        $dt = clone $this->showMinDate;
        while ($dt < $this->showMaxDate) {
            $dateElem = $dateRow->appendChild($domtree->createElement('date', $dt->format('d.m.Y')));
            
            if(isset($this->bookingDates[$dt->format('d.m.Y')])) {   // booking exists on this date
                $attrState = $domtree->createAttribute('state');
                $attrState->value = $this->bookingDates[$dt->format('d.m.Y')]->status;
                if ($attrState->value == '') {   // status has been reset
                    $attrState->value = 'available';
                }
                $dateElem->appendChild($attrState);
                
                // this flag is used to show a checkedout allocation as a different colour (for a block of dates)
                $attrCheckedOut = $domtree->createAttribute('checkedout');
                $attrCheckedOut->value = $this->bookingDates[$dt->format('d.m.Y')]->checkedOut ? 'true' : 'false';
                $dateElem->appendChild($attrCheckedOut);
                        
                // if booking date is Paid/Hours/Free and no booking on the following date, then it is a "checkout" day
                // append the "checkedoutset" attribute if it exists so we can display an icon to checkout/un-checkout
                if ($attrState->value == 'paid' || $attrState->value == 'hours' || $attrState->value == 'free') {
                    $dt_after = clone $dt;
                    $dt_after->add(new DateInterval('P1D'));
                    if (false === isset($this->bookingDates[$dt_after->format('d.m.Y')]) 
                            || $this->bookingDates[$dt_after->format('d.m.Y')]->status == '') {
                        $attrCheckedOutSet = $domtree->createAttribute('checkedoutset');
                        $attrCheckedOutSet->value = $this->bookingDates[$dt->format('d.m.Y')]->checkedOut ? 'true' : 'false';
                        $dateElem->appendChild($attrCheckedOutSet);
                    }
                }

            } else { // booking doesn't exist
                $attrState = $domtree->createAttribute('state');
                $attrState->value = 'available';
                $dateElem->appendChild($attrState);
            }
            $dt->add(new DateInterval('P1D'));  // increment by day
        }
    }
    
    /** 
      Generates the following xml:
        <allocation>
            <rowid>3</rowid>
            <id>31</id>
            <name>Megan-1</name>
            <gender>Female</gender>
            <reqRoomSize>4</reqRoomSize>
            <reqRoomType>F</reqRoomType>
            <resourceid>21</resourceid>
            <resource>Bed A</resource>
            <parentresource>Room 12</parentresource>
            <bookingsBeforeMinDate>0</bookingsBeforeMinDate>
            <bookingsAfterMaxDate>3</bookingsAfterMaxDate>
            <isAvailable>true</isAvailable>
            <dates>
                <date state="paid" checkedout="true">15.08.2012</date>
                <date state="paid" checkedout="true" checkedoutset="true">16.08.2012</date>
            </dates>
        </allocation>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
}

?>