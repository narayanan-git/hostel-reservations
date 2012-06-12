<?php

/**
 * Encapsulates and renders a table containing all allocations for a booking.
 */
class AllocationTable {
    var $showMinDate;   // minimum date to show on the table
    var $showMaxDate;   // maximum date to show on the table
    private $allocationRows = array();  // array of AllocationRow
    private $allocationStrategy;
    
    function AllocationTable() {
        $this->allocationStrategy = new AllocationStrategy();
    }

    /**
     * Adds a number of allocations with the specified attributes.
     * numVisitors : number of guests to add
     * bookingName : name booking is under
     * gender : Male/Female
     * resourceId : id of resource to allocate to
     * dates : comma-delimited list of dates in format dd.MM.yyyy
     * Throws AllocationException if there aren't enough "leaf" resources to add the given
     *        number of allocations.
     */
    function addAllocation($bookingName, $numVisitors, $gender, $resourceId, $dates) {
        $datearr = explode(",", $dates);
        $bookingName = trim($bookingName) == '' ? 'Unspecified' : $bookingName;
        $newAllocationRows = array();  // the new allocations we will be adding
        for($i = 0; $i < $numVisitors; $i++) {
            $allocationRow = new AllocationRow($bookingName.'-'.(sizeof($this->allocationRows) + sizeof($newAllocationRows)+1), $gender, $resourceId);
            foreach ($datearr as $dt) {
                $allocationRow->addPaymentForDate(trim($dt), 15); // FIXME: price fixed at 15
            }
            $newAllocationRows[] = $allocationRow;
        }
        // this will perform the individual assignments to beds if a parent resource id is specified
        $this->allocationStrategy->assignResourcesForAllocations($newAllocationRows, $this->allocationRows);
        
        // check that all of the ones we just added have been assigned "leaf" resources (beds)
        foreach ($newAllocationRows as $newAlloc) {
            if (false == $newAlloc->isAvailable) {
error_log("AllocationTable::addAllocation throws ex ".$newAlloc->resourceId . " with name ". $newAlloc->name);
                throw new AllocationException("Insufficient resources to allocate $bookingName.");
            }
else error_log("AllocationTable::addAllocation OK ".$newAlloc->resourceId . " with name ". $newAlloc->name);
        }
error_log("Allocating ".sizeof($newAllocationRows));
        // allocation successful; add them to the existing ones we have for this booking
        foreach ($newAllocationRows as $newAlloc) {
            $this->allocationRows[] = $newAlloc;
            // keep the unique id for the row so we can reference it later when updating via ajax
            $newAlloc->rowid = array_search($newAlloc, $this->allocationRows);
error_log("assigning row id ".$newAlloc->rowid." to ".$newAlloc->resourceId);
        }
    }
    
    /**
     * This will set the showMinDate, showMaxDate properties to their default values.
     * showMinDate will be set to 3 days prior to the minimum date on allocationRows
     * showMaxDate will be set to 14 days after showMinDate
     */
    function setDefaultMinMaxDates() {
        $result = null; 
        
        // first find the min date
        foreach ($this->allocationRows as $allocation) {
            $minRowDate = $allocation->getMinDate();
            if($result == null || $minRowDate < $result) {
                $result = $minRowDate;
            }
        }
        
        if($result != null) {
            $result->sub(new DateInterval('P3D'));  // default to 3 days prior
            $this->showMinDate = $result;
            $this->showMaxDate = clone $result;
            $this->showMaxDate->add(new DateInterval('P14D'));  // default to 14 days after
        }
    }
    
    /**
     * Calculates total payment by summing all allocation rows.
     * Returns: numeric value
     */
    function getTotalPayment() {
        $result = 0;
        foreach ($this->allocationRows as $allocation) {
            $result += $allocation->getTotalPayment();
        }
        return $result;
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
        $ar = $this->allocationRows[$rowid];
        // do we need a null check?
        // TODO: toggle status on current date
        if($ar != null && $ar->isExistsBooking($dt)) {
            $ar->removePaymentForDate($dt);
            return 'available';
        } else {
            // if it doesn't exist, then add it
            $ar->addPaymentForDate($dt, 15); // FIXME: price fixed at 15
            return 'pending';
        }
    }
    
    /**
     * Validates the stuff in this table.
     * Returns an error of string values, one for each error message.
     * An empty array obviously means no errors.
     */
    function doValidate() {
        $errors = array();
        if(sizeof($this->allocationRows) == 0) {
            $errors[] = 'No allocations have been added';
        }
        foreach ($this->allocationRows as $row) {
            if(! $row->isExistsBooking()) {
                $errors[] = $row->name . ' does not have any dates selected';
            }
        }
        return $errors;
    }

    /**
     * Saves all allocations to the db.
     * $mysqli : manual db connection (for transaction handling)
     * $bookingId : booking id for this allocation
     * Throws AllocationException if one or more allocations failed due to lack of availability
     */
    function save($mysqli, $bookingId) {
    
        $failedAllocation = false;
        foreach ($this->allocationRows as $alloc) {
            $alloc_id = $alloc->save($mysqli, $bookingId);

            if( ! $alloc->isAvailable) {
                $failedAllocation = true;
            }
        }
        
        // report business error if demand > supply
        // TODO: move this down to bookingdates level
        if ($failedAllocation) {
            throw new AllocationException("One or more allocations did not have sufficient availability");
        }

    }
    
    /** 
      Generates the following xml:
        <allocations total="49.50">
            <bookingName>Megan</bookingName>
            <showMinDate>25.08.2012</showMinDate>
            <showMaxDate>04.09.2012</showMaxDate>
            <allocation>...</allocation>
            <allocation>...</allocation>
            <dateheaders>
                <header>August/September</header>
                <datecol>
                    <date>25</date>
                    <day>Sun</day>
                <datecol>
                <datecol>
                    <date>26</date>
                    <day>Mon</day>
                <datecol>
                ...
            </dateheaders>
        </allocations>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
    
        // create the root element of the xml tree
        $xmlRoot = $domtree->createElement('allocations');
        $xmlRoot = $domtree->appendChild($xmlRoot);
    
        if($this->showMinDate != null) {
            $xmlRoot->appendChild($domtree->createElement('showMinDate', $this->showMinDate->format('d.m.Y')));
        }
        if($this->showMaxDate != null) {
            $xmlRoot->appendChild($domtree->createElement('showMaxDate', $this->showMaxDate->format('d.m.Y')));
        }

        $attrTotal = $domtree->createAttribute('total');
        $attrTotal->value = $this->getTotalPayment();
        $xmlRoot->appendChild($attrTotal);
        foreach ($this->allocationRows as $allocation) {
            $allocation->showMinDate = $this->showMinDate;
            $allocation->showMaxDate = $this->showMaxDate;
            $allocation->addSelfToDocument($domtree, $xmlRoot);
        }

        // build dateheaders to be used to display availability table
        if($this->showMinDate != null && $this->showMaxDate != null) {
            $dateHeaders = $xmlRoot->appendChild($domtree->createElement('dateheaders'));
            
            // if spanning more than one month, print out both months
            if($this->showMinDate->format('F') !== $this->showMaxDate->format('F')) {
                $dateHeaders->appendChild($domtree->createElement('header', $this->showMinDate->format('F') . '/' . $this->showMaxDate->format('F')));
            } else {
                $dateHeaders->appendChild($domtree->createElement('header', $this->showMinDate->format('F')));
            }
            
            $dt = clone $this->showMinDate;
            while ($dt < $this->showMaxDate) {
                $dateElem = $dateHeaders->appendChild($domtree->createElement('datecol'));
                $dateElem->appendChild($domtree->createElement('date', $dt->format('d')));
                $dateElem->appendChild($domtree->createElement('day', $dt->format('D')));
                $dt->add(new DateInterval('P1D'));  // increment by day
            }
        }
        return $domtree->saveXML();
    }
    
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/allocation_table.xsl');
        
        // import the XSL styelsheet into the XSLT process
        $xp = new XsltProcessor();
        $xp->importStylesheet($xsl);
        
        // create a DOM document and load the XML datat
        $xml_doc = new DomDocument;
        $xml_doc->loadXML($this->toXml());
        
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