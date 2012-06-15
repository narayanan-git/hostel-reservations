<?php

/**
 * Encapsulates one row in the allocation table (one bed for one person)
 * and renders it.
 */
class AllocationRow {
    var $rowid;  // unique id for this row prior to creation
    var $id;  // TODO: can consolidate id => rowid
    var $name;
    var $status;  // checkedin, checkedout, pending, etc
    var $gender;
    var $resourceId;
    var $showMinDate;   // minimum date to show on the table (DateTime)
    var $showMaxDate;   // maximum date to show on the table (DateTime)
    var $isAvailable;   // boolean : set this flag to true/false during allocation process (if resource is available or not)
    var $editMode;  // either 'edit' or 'add'
    private $bookingDatePayment = array();  // key = booking date, value = payment amount
    private $resourceMap;  // array of resource_id -> resource_name

    function AllocationRow($name, $gender, $resourceId, $status = 'pending') {
        $this->name = $name;
        $this->gender = $gender;
        $this->resourceId = $resourceId;
        $this->status = $status;
        $this->resourceMap = ResourceDBO::getResourceMap();
        $this->editMode = 'add'; // default unless set
    }

    /**
     * Sets rendering of allocation as an existing one.
     */
    function setEditMode() {
        $this->editMode = 'edit';
    }
    
    /**
     * Sets rendering of allocation as a new one.
     */
    function setAddMode() {
        $this->editMode = 'add';
    }
    
    /**
     * Adds a date/payment entry for this allocation row
     * $dt  date string in format dd.MM.yyyy
     * $payment  value of payment for specified date
     */
    function addPaymentForDate($dt, $payment) {
        $this->bookingDatePayment[$dt] = $payment;
    }

    /**
     * Removes the booking for a date.
     * $dt  date string in format dd.MM.yyyy
     */
    function removePaymentForDate($dt) {
        // if payment doesn't exist, the booking doesn't exist
        unset($this->bookingDatePayment[$dt]);
    }
    
    /**
     * Checks whether a booking exists on a particular date.
     * If no date is specified, checks whether a booking exists on *any* date.
     * $dt  date string in format dd.MM.yyyy  (optional)
     */
    function isExistsBooking($dt = '') {
        if($dt == '') {
            return sizeof($this->bookingDatePayment) > 0;
        }
        return isset($this->bookingDatePayment[$dt]);
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
        foreach ($this->bookingDatePayment as $bookingDate => $payment) {
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
        foreach ($this->bookingDatePayment as $bookingDate => $payment) {
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
        return array_keys($this->bookingDatePayment);
    }
    
    /**
     * Calculates total payment by summing bookingDatePayment
     * Returns: numeric value
     */
    function getTotalPayment() {
        $result = 0;
        foreach ($this->bookingDatePayment as $bookingDate => $payment) {
            $result += $payment;
        }
        return $result;
    }

    /**
     * Returns the number of bookings for this allocation before showMinDate.
     */
    function getNumBookingsBeforeMinDate() {
        $result = 0;
        if($this->showMinDate != null) {
            foreach ($this->bookingDatePayment as $bookingDate => $payment) {
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
            foreach ($this->bookingDatePayment as $bookingDate => $payment) {
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
            $mysqli, $bookingId, $this->resourceId, $this->name, $this->status, substr($this->gender, 0, 1));
error_log("inserted allocation $allocationId");
        // then create the booking dates for the allocation
        $this->isAvailable = true;
        foreach (array_keys($this->bookingDatePayment) as $bookingDate) {
error_log("to insert $bookingDate");
            // any booking date that breaks availability will flag it up at the row level
            // TODO: should move this onto the booking date field
            if( ! AllocationDBO::insertBookingDate($mysqli, $this->resourceId, $allocationId, $bookingDate)) {
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
    
        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('rowid', $this->rowid));
        $xmlRoot->appendChild($domtree->createElement('name', $this->name));
        $xmlRoot->appendChild($domtree->createElement('gender', $this->gender));
        $xmlRoot->appendChild($domtree->createElement('resource', $this->resourceMap[$this->resourceId]));

        $dateRow = $domtree->createElement('dates');
        $attrTotal = $domtree->createAttribute('total');
        $attrTotal->value = $this->getTotalPayment();
        $dateRow->appendChild($attrTotal);
        $xmlRoot->appendChild($dateRow);
        
        // initialise min/max dates if not set
        if($this->showMinDate == null) {
            $this->showMinDate = $this->getMinDate();
        }
        if($this->showMaxDate == null) {
            $this->showMaxDate = $this->getMaxDate();
        }
        
        // adding new allocations, we generate entries for all dates within min/max
        if($this->editMode == "add") {

            $xmlRoot->appendChild($domtree->createElement('bookingsBeforeMinDate', $this->getNumBookingsBeforeMinDate()));
            $xmlRoot->appendChild($domtree->createElement('bookingsAfterMaxDate', $this->getNumBookingsAfterMaxDate()));
            $xmlRoot->appendChild($domtree->createElement('isAvailable', $this->isAvailable ? 'true' : 'false'));
            
            // loop from showMinDate to showMaxDate creating a date element for every day in between
            // set the appropriate state if a booking exists for that date
            $dt = clone $this->showMinDate;
            while ($dt < $this->showMaxDate) {
                $dateElem = $dateRow->appendChild($domtree->createElement('date', $dt->format('d.m.Y')));
                
                if(isset($this->bookingDatePayment[$dt->format('d.m.Y')])) {   // also means that booking exists on this date
                    $payment = $this->bookingDatePayment[$dt->format('d.m.Y')];
                    $attrPayment = $domtree->createAttribute('payment');
                    $attrPayment->value = $payment;
                    $dateElem->appendChild($attrPayment);
    
                    $attrState = $domtree->createAttribute('state');
                    $attrState->value = 'pending';
                    $dateElem->appendChild($attrState);
                
                // TODO: different state when date is in the past?
    
                } else { // booking doesn't exist
                    $attrPayment = $domtree->createAttribute('payment');
                    $attrPayment->value = 15;  // TODO: add payment rules
                    $dateElem->appendChild($attrPayment);
                    
                    $attrState = $domtree->createAttribute('state');
                    $attrState->value = 'available';
                    $dateElem->appendChild($attrState);
                }
                $dt->add(new DateInterval('P1D'));  // increment by day
            }
        }

        // editing existing allocations, we just generate the dates which exist for the allocation
        if($this->editMode == "edit") {
            foreach ($this->bookingDatePayment as $bookingDate => $payment) {
                $dateElem = $dateRow->appendChild($domtree->createElement('date', $bookingDate));
                $attrPayment = $domtree->createAttribute('payment');
                $attrPayment->value = 15;  // TODO: add payment rules
                $dateElem->appendChild($attrPayment);
            }
        }
    }
    
    /** 
      Generates the following xml:
        <allocation>
            <id>3</id>
            <name>Megan-1</name>
            <gender>Female</gender>
            <resource>Bed A</resource>
            <bookingsBeforeMinDate>0</bookingsBeforeMinDate>
            <bookingsAfterMaxDate>3</bookingsAfterMaxDate>
            <isAvailable>true</isAvailable>
            <dates total="24.90">
                <date payment="12.95" state="checkedin">15.08.2012</date>
                <date payment="12.95" state="pending">16.08.2012</date>
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