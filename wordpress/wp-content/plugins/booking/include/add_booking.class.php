<?php

/**
 * Controller for Add Booking form.
 */
class AddBooking extends XslTransform {

    var $id;
    var $firstname;
    var $lastname;
    var $referrer;
    var $depositPaid;  // deposit paid as decimal
    var $amountToPay;  // amount to pay on arrival as decimal
    
    // all allocations for this booking (type AllocationTable)
    private $allocationTable;
    private $commentLog;    // BookingCommentLog
    private $resourceMap;   // const map of resources 

    function AddBooking() {
        $this->id = 0;
        $this->resourceMap = ResourceDBO::getAllResources();
        $this->allocationTable = new AllocationTable($this->resourceMap);
        $this->commentLog = new BookingCommentLog();
    }
    
    /**
     * Adds a number of allocations with the specified attributes.
     * numVisitors : number of guests to add, array indexed by 'M', 'F', 'X'
     * resourceId : id of resource to allocate to (null for any)
     * reqRoomSize : requested room size (e.g. 8, 10, 10+, P, etc..)
     * reqRoomType : requested room type (M/F/X)
     * dates : array of dates (String) in format dd.MM.yyyy
     * resourceProps : array of resource property ids (allocate only to resources with these properties)
     */
    function addAllocation($numVisitors, $resourceId, $reqRoomSize, $reqRoomType, $dates, $resourceProps) {
error_log("addAllocation $resourceId ".var_export($numVisitors, true));
        $this->allocationTable->addAllocation($this->firstname, $numVisitors, $resourceId, 
            $reqRoomSize, $reqRoomType == null ? "X" : $reqRoomType, $dates, $resourceProps);
        
        if($this->allocationTable->showMinDate == null || $this->allocationTable->showMaxDate == null) {
            $this->allocationTable->setDefaultMinMaxDates();
        }
    }
    
    /**
     * Returns the html for the current allocation table
     */
    function getAllocationTableHtml() {
        return $this->allocationTable->toHtml();
    }
    
    /**
     * Returns the html for the comment log
     */
    function getCommentLogHtml() {
        return $this->commentLog->toHtml();
    }
    
    /**
     * Validates the stuff on this controller.
     * Returns an error of string values, one for each error message.
     * An empty array obviously means no errors.
     */
    function doValidate() {
        $errors = array();
        if (trim($this->firstname) == '') {
            $errors[] = 'First name cannot be blank';
        }
        if (empty($this->allocationTable)) {
            $errors[] = 'You must add at least one allocation';
        }
        foreach ($this->allocationTable->doValidate() as $atError) {
            $errors[] = $atError;
        }
        return $errors;
    }

    /**
     * Toggles the gender for the given allocation row
     * $rowid : id of allocation row to toggle
     */
    function toggleGender($rowid) {
        $this->allocationTable->toggleGender($rowid);
    }
    
    /**
     * This will update the state of a booking allocation.
     * $rowid : unique id of allocation row
     * $dt : date if format 'd.m.Y'
     * Returns: state of current allocation on this date (one of 'pending', 'available', 'checkedin', 'checkedout', 'noshow')
     */
    function toggleBookingStateAt($rowid, $dt) {
        return $this->allocationTable->toggleBookingStateAt($rowid, $dt);
    }
    
    /**
     * This will toggle the checkout state of a booking allocation for a set of contiguous dates.
     * $rowid : unique id of allocation row
     * $dt : date if format 'd.m.Y'
     */
    function toggleCheckoutOnBookingDate($rowid, $dt) {
        $this->allocationTable->toggleCheckoutOnBookingDate($rowid, $dt);
    }
    
    /**
     * Enables editing fields on the given allocation row.
     * $rowid : unique id of allocation row
     */
    function enableEditOnAllocation($rowid) {
        $this->allocationTable->enableEditOnAllocation($rowid);
    }
    
    /**
     * Disables editing fields on the given allocation row.
     * $rowid : unique id of allocation row
     */
    function disableEditOnAllocation($rowid) {
        $this->allocationTable->disableEditOnAllocation($rowid);
    }
    
    /**
     * Updates the name, resource fields on the given allocation row.
     * $rowid : unique id of allocation row
     * $allocationName : name of guest
     * $resourceId : valid resource id (can be parent)
     */
    function updateAllocationRow($rowid, $allocationName, $resourceId) {
        $this->allocationTable->updateAllocationRow($rowid, $allocationName, $resourceId);
    }

    /**
     * Removes the given allocation row.
     * $rowid : unique id of allocation row
     */
    function deleteAllocationRow($rowid) {
        $this->allocationTable->deleteAllocationRow($rowid);
    }

    /**
     * Moves the reference dates to the right
     */
    function shiftCalendarRight() {
        // default is by 13 days (2 columns in previous table are now on the far left)
        $this->allocationTable->showMinDate->add(new DateInterval('P13D'));
        $this->allocationTable->showMaxDate->add(new DateInterval('P13D'));
    }

    /**
     * Moves the reference dates to the left
     */
    function shiftCalendarLeft() {
        // default is by 13 days (2 columns in previous table are now on the far right)
        $this->allocationTable->showMinDate->sub(new DateInterval('P13D'));
        $this->allocationTable->showMaxDate->sub(new DateInterval('P13D'));
    }
    
    /**
     * Adds a comment to this booking. This will persist the comment immediately if
     * the booking has already been saved. Otherwise it will just add the comment to be
     * saved later.
     * $comment : non-empty comment
     */
    function addComment($comment, $commentType) {
        $this->commentLog->comments[] = new BookingComment($this->id, $comment, $commentType);
        
        // persist the comment only if the booking already exists
        if ($this->id > 0) {
            $dblink = new DbTransaction();
            try {
                $this->commentLog->save($dblink->mysqli, $this->id);
                $dblink->mysqli->commit();
                $dblink->mysqli->close();
                
                // reload the comments so the ids are set and we don't save them twice
                $this->commentLog->load($this->id);
    
            } catch(Exception $e) {
                $dblink->mysqli->rollback();
                $dblink->mysqli->close();
                throw $e;
            }
        }
    }
    
    /**
     * Saves this booking and all allocations to the db.
     */
    function save() {
    
        if ($this->id == 0) {  // new record
            $this->id = BookingDBO::insertNewBooking($this->firstname, $this->lastname, $this->referrer, $this->depositPaid, $this->amountToPay, $this->allocationTable, $this->commentLog);
error_log("inserted booking id $this->id");

        } else { // existing record
            BookingDBO::updateExistingBooking($this->id, $this->firstname, $this->lastname, $this->referrer, $this->depositPaid, $this->amountToPay, $this->allocationTable);
error_log("updated booking id $this->id");
        }

        // once everything has been saved, reload everything from db...
        // this will set the ids on everything so saving again will do update not insert
        $this->load($this->id);
error_log("reloaded booking $this->id");

    }
    
    /**
     * Loads the data for this object from an existing booking id.
     * $bookingId  : id of existing booking
     */
    function load($bookingId) {
        $rs = BookingDBO::fetchBookingDetails($bookingId);
        $this->id = $rs->booking_id;
        $this->firstname = $rs->firstname;
        $this->lastname = $rs->lastname;
        $this->referrer = $rs->referrer;
        $this->depositPaid = $rs->deposit_paid;
        $this->amountToPay = $rs->amount_to_pay;
        $this->allocationTable->load($bookingId);
        $this->commentLog->load($bookingId);
    }

    /** 
      Generates the following xml:
        <editbooking>
            <id>25</id>
            <firstname>Megan</firstname>
            <lastname>Fox</lastname>
            <referrer>telephone</referrer>
            <depositpaid>10.70</depositpaid>
            <amounttopay>91.35</amounttopay>
            <allocations>
                <bookingName>Megan-1</bookingName>
                ...
            </allocations>
            <comments>
                <comment>...<comment>
                ...
            </comments>
            <properties>
                <property>...</property>
            </properties>
        </editbooking>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
    
        // create the root element of the xml tree
        $xmlRoot = $domtree->createElement('editbooking');
        $xmlRoot = $domtree->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('homeurl', home_url()));
        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('firstname', $this->firstname));
        $xmlRoot->appendChild($domtree->createElement('lastname', $this->lastname));
        $xmlRoot->appendChild($domtree->createElement('referrer', $this->referrer));
        $xmlRoot->appendChild($domtree->createElement('depositpaid', number_format($this->depositPaid, 2, '.', '')));
        $xmlRoot->appendChild($domtree->createElement('amounttopay', number_format($this->amountToPay, 2, '.', '')));
        
        // add current allocations
        $this->allocationTable->addSelfToDocument($domtree, $xmlRoot);

        // add comments
        $this->commentLog->addSelfToDocument($domtree, $xmlRoot);
        
        $propRoot = $xmlRoot->appendChild($domtree->createElement('properties'));
        foreach (ResourceDBO::getPropertiesForResource() as $prop) {
            $propRow = $domtree->createElement('property');
            $propRow->appendChild($domtree->createElement('id', $prop->property_id));
            $propRow->appendChild($domtree->createElement('value', $prop->description));
            $propRoot->appendChild($propRow);
        }

error_log($domtree->saveXML());
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/add_booking.xsl';
    }
}

?>