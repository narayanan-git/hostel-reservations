<?php

/**
 * Controller for Add Booking form.
 */
class AddBooking {

    var $id;
    var $firstname;
    var $lastname;
    var $referrer;
    var $details;
    
    // all allocations for this booking (type AllocationTable)
    var $allocationTable;

    function AddBooking() {
        $this->id = 0;
        $this->allocationTable = new AllocationTable();
    }
    
    /**
     * Adds a number of allocations with the specified attributes.
     * numVisitors : number of guests to add
     * gender : Male/Female
     * resourceId : id of resource to allocate to
     * dates : comma-delimited list of dates in format dd.MM.yyyy
     */
    function addAllocation($numVisitors, $gender, $resourceId, $dates) {
error_log("addAllocation $numVisitors, $gender, $resourceId");
        $this->allocationTable->addAllocation($this->firstname, $numVisitors, $gender, $resourceId, $dates);
        
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
     * Validates the stuff on this controller.
     * Returns an error of string values, one for each error message.
     * An empty array obviously means no errors.
     */
    function doValidate() {
        $errors = array();
        if (trim($this->firstname) == '') {
            $errors[] = 'First name cannot be blank';
        }
        foreach ($this->allocationTable->doValidate() as $atError) {
            $errors[] = $atError;
        }
        return $errors;
    }
    
    /**
     * This will update the state of a booking allocation.
     * Rules:
     *    if date is in the future, this will add/remove the current allocation at this date
     *    if date is today, this will toggle state between checkedin, checkedout, noshow
     *    if date is in the past, this will do nothing
     * Returns: state of current allocation on this date (one of 'pending', 'available', 'checkedin', 'checkedout', 'noshow')
     */
    function toggleBookingStateAt($rowid, $dt) {
        return $this->allocationTable->toggleBookingStateAt($rowid, $dt);
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
     * Saves this booking and all allocations to the db.
     */
    function save() {
        $dblink = new DbTransaction();
        try {
            $bookingId = BookingDBO::insertBooking($dblink->mysqli, $this->firstname, $this->lastname, $this->referrer,  wp_get_current_user()->user_login);
error_log("inserted booking id $bookingId");
            $this->allocationTable->save($dblink->mysqli, $bookingId);
            $dblink->mysqli->commit();
            $dblink->mysqli->close();

        } catch(Exception $e) {
            $dblink->mysqli->rollback();
            $dblink->mysqli->close();
            throw $e;
        }
    }

    /** 
      Generates the following xml:
        <editbooking>
            <id>25</id>
            <firstname>Megan</firstname>
            <lastname>Fox</lastname>
            <referrer>telephone</referrer>
            <allocations>
                <bookingName>Megan-1</bookingName>
                ...
            </allocations>
            <resources>
                <resource>
                    <id>1</id>
                    <name>8-Bed Dorm</name>
                    <level>1</level>
                </resource>
                <resource>
                    <id>2</id>
                    <name>Bed A</name>
                    <level>2</level>
                </resource>
                ...
            </resources>
        </editbooking>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
    
        // create the root element of the xml tree
        $xmlRoot = $domtree->createElement('editbooking');
        $xmlRoot = $domtree->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('firstname', $this->firstname));
        $xmlRoot->appendChild($domtree->createElement('lastname', $this->lastname));
        $xmlRoot->appendChild($domtree->createElement('referrer', $this->referrer));
        
        // add current allocations
        $this->allocationTable->addSelfToDocument($domtree, $xmlRoot);

        $resourcesRoot = $domtree->createElement('resources');
        $xmlRoot = $xmlRoot->appendChild($resourcesRoot);

        foreach (ResourceDBO::getAllResources() as $res) {
            $resourceRow = $domtree->createElement('resource');
            $resourceRow->appendChild($domtree->createElement('id', $res->resource_id));
            $resourceRow->appendChild($domtree->createElement('name', $res->name));
            $resourceRow->appendChild($domtree->createElement('level', $res->lvl));
            $resourcesRoot->appendChild($resourceRow);
        }
        return $domtree->saveXML();
    }
    
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/add_booking.xsl');
        
        // import the XSL styelsheet into the XSLT process
        $xp = new XsltProcessor();
        $xp->importStylesheet($xsl);
        
        // create a DOM document and load the XML datat
        $xml_doc = new DomDocument;
        $xml_doc->loadXML($this->toXml());
error_log($this->toXml());

        // transform the XML into HTML using the XSL file
        if ($html = $xp->transformToXML($xml_doc)) {
            return $html;
        } else {
            trigger_error('XSL transformation failed.', E_USER_ERROR);
        } // if 
        return 'XSL transformation failed.';
    }
}

?>