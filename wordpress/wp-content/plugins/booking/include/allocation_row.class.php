<?php

/**
 * Encapsulates one row in the allocation table (one bed for one person)
 * and renders it.
 */
class AllocationRow {
    var $rowid;  // unique id for this row prior to creation
    var $name;
    var $gender;
    var $resourceId;
    var $showMinDate;   // minimum date to show on the table (DateTime)
    var $showMaxDate;   // maximum date to show on the table (DateTime)
    var $isAvailable;   // boolean : set this flag to true/false during allocation process (if resource is available or not)
    var $editMode;  // either 'edit' or 'add'
    var $bookingDateStatus = array();  // key = booking date, value = status
    var $mode = 'view';   // either 'view' or 'edit'
    private $resourceMap;  // array of resource_id -> resource recordset
    private $STATUSES = array('reserved', 'paid', 'free', 'hours', 'cancelled'); 

    /**
     * Default constructor.
     * $name : guest name
     * $gender : guest gender
     * $resourceId : resource to assign to guest
     * $status : current status of allocation (default: reserved)
     * $resourceMap : array() of resource id -> resource recordset (if not specified, query db for all resources)
     */
    function AllocationRow($name, $gender, $resourceId, $resourceMap = null) {
        $this->name = $name;
        $this->gender = $gender;
        $this->resourceId = $resourceId;
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
error_log("in toggleStatusForDate $dt");
        if(false === isset($this->bookingDateStatus[$dt])) {
error_log("$dt not set, setting to ".$this->STATUSES[0]);
            $this->bookingDateStatus[$dt] = $this->STATUSES[0];

        } else {
error_log("$dt set");
            $key = array_search($this->bookingDateStatus[$dt], $this->STATUSES);
error_log("key is $key");
            
            // if we are at the end of STATUSES, unset the date so it's "available"
            if ($key === sizeof($this->STATUSES) - 1) {
error_log("end of the line, unsetting $dt");
                unset($this->bookingDateStatus[$dt]);
            }
            $this->bookingDateStatus[$dt] = $this->STATUSES[$key + 1];
error_log("toggleStatusForDate $dt setting to ".$this->bookingDateStatus[$dt]);            
            //TODO: if 'C' check if is @ start of block and cancel all subsequent dates
        }
        return $this->bookingDateStatus[$dt];
    }

    /**
     * Checks whether a booking exists on a particular date.
     * If no date is specified, checks whether a booking exists on *any* date.
     * $dt  date string in format dd.MM.yyyy  (optional)
     */
    function isExistsBooking($dt = '') {
        if($dt == '') {
            return sizeof($this->bookingDateStatus) > 0;
        }
        return isset($this->bookingDateStatus[$dt]);
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
        foreach ($this->bookingDateStatus as $bookingDate => $status) {
            $bd = DateTime::createFromFormat('!d.m.Y', $bookingDate, new DateTimeZone('UTC'));
            if($result == null || $bd < $result) {
                $result = $bd;
            }
        }
        return $result;
    }
    
    /**
     * Returns the maximum date (DateTime) for this allocation.
     */
    function getMaxDate() {
        $result = null;
        foreach ($this->bookingDateStatus as $bookingDate => $status) {
            $bd = DateTime::createFromFormat('!d.m.Y', $bookingDate, new DateTimeZone('UTC'));
            if($result == null || $bd > $result) {
                $result = $bd;
            }
        }
        return $result;
    }
    
    /**
     * Returns array of booking dates (string) for this AllocationRow.
     */
    function getBookingDates() {
        return array_keys($this->bookingDateStatus);
    }
    
    /**
     * Returns the number of bookings for this allocation before showMinDate.
     */
    function getNumBookingsBeforeMinDate() {
        $result = 0;
        if($this->showMinDate != null) {
            foreach ($this->bookingDateStatus as $bookingDate => $status) {
                $bd = DateTime::createFromFormat('!d.m.Y', $bookingDate, new DateTimeZone('UTC'));
                if($bd < $this->showMinDate) {
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
            foreach ($this->bookingDateStatus as $bookingDate => $status) {
                $bd = DateTime::createFromFormat('!d.m.Y', $bookingDate, new DateTimeZone('UTC'));
                if($bd > $this->showMaxDate) {
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
        
        // create the allocation
        $allocationId = AllocationDBO::insertAllocation(
            $mysqli, $bookingId, $this->resourceId, $this->name, $this->gender);
error_log("inserted allocation $allocationId");
        // then create the booking dates for the allocation
        $this->isAvailable = true;
        foreach ($this->bookingDateStatus as $bookingDate => $status) {
error_log("to insert $bookingDate , $status");
            // any booking date that breaks availability will flag it up at the row level
            // TODO: should move this onto the booking date field
            if( false === AllocationDBO::insertBookingDate($mysqli, $this->resourceId, $allocationId, $bookingDate, $status)) {
                $this->isAvailable = false;
            }
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
        $xmlRoot->appendChild($domtree->createElement('mode', $this->mode));
        $xmlRoot->appendChild($domtree->createElement('name', $this->name));
        $xmlRoot->appendChild($domtree->createElement('gender', $this->gender));
        $xmlRoot->appendChild($domtree->createElement('resourceid', $this->resourceId));
        $xmlRoot->appendChild($domtree->createElement('resource', $this->resourceMap[$this->resourceId]->name));
        $xmlRoot->appendChild($domtree->createElement('parentresource', $this->resourceMap[$this->resourceId]->parent_name));

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
            
            if(isset($this->bookingDateStatus[$dt->format('d.m.Y')])) {   // also means that booking exists on this date
                $attrState = $domtree->createAttribute('state');
                $attrState->value = $this->bookingDateStatus[$dt->format('d.m.Y')];
                $dateElem->appendChild($attrState);

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
            <mode>view</mode>
            <name>Megan-1</name>
            <gender>Female</gender>
            <resourceid>21</resourceid>
            <resource>Bed A</resource>
            <parentresource>Room 12</parentresource>
            <bookingsBeforeMinDate>0</bookingsBeforeMinDate>
            <bookingsAfterMaxDate>3</bookingsAfterMaxDate>
            <isAvailable>true</isAvailable>
            <dates>
                <date state="reserved">15.08.2012</date>
                <date state="reserved">16.08.2012</date>
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