<?php

/**
 * Encapsulates and renders a table containing all allocations for a booking.
 */
class AllocationTable extends XslTransform {
    var $showMinDate;   // minimum date to show on the table (DateTime)
    var $showMaxDate;   // maximum date to show on the table (DateTime)
    var $editingRowId;  // row id of allocation currently being edited
    var $allocationRows = array();  // array of AllocationRow
    private $allocationStrategy;
    private $resourceMap;   // const map of resources 
    
    /**
     * Default constructor.
     * $resourceMap : (optional) map of resource id -> resource recordset
     *                if not set, all resources will be fetched from dbo
     */
    function AllocationTable($resourceMap = null) {
        $this->resourceMap = $resourceMap == null ? ResourceDBO::getAllResources() : $resourceMap;
        $this->allocationStrategy = new AllocationStrategy($this->resourceMap);
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
            $allocationRow = new AllocationRow($bookingName.'-'.(sizeof($this->allocationRows) + sizeof($newAllocationRows)+1), $gender, $resourceId, $this->resourceMap);
            foreach ($datearr as $dt) {
                $allocationRow->toggleStatusForDate(trim($dt));
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
     * This will update the state of a booking allocation.
     * $rowid : unique id of allocation row
     * $dt : date if format 'd.m.Y'
     * Returns: state of current allocation on this date (one of 'pending', 'available', 'checkedin', 'checkedout', 'noshow')
     */
    function toggleBookingStateAt($rowid, $dt) {
        $ar = $this->allocationRows[$rowid];
        return $ar->toggleStatusForDate($dt);
    }
    
    /**
     * This will toggle the checkout state of a booking allocation for a set of contiguous dates.
     * $rowid : unique id of allocation row
     * $dt : date if format 'd.m.Y'
     */
    function toggleCheckoutOnBookingDate($rowid, $dt) {
        $ar = $this->allocationRows[$rowid];
        $ar->toggleCheckoutStatusForDate($dt);
    }
    
    /**
     * Enables editing fields on the given allocation row.
     * $rowid : unique id of allocation row
     */
    function enableEditOnAllocation($rowid) {
        if (isset($this->allocationRows[$rowid])) {
            $this->editingRowId = $rowid;
        }
    }
    
    /**
     * Disables editing fields on the given allocation row.
     * $rowid : unique id of allocation row
     */
    function disableEditOnAllocation($rowid) {
        if (isset($this->allocationRows[$rowid])) {
            $this->editingRowId = null;
        }
    }
    
    /**
     * Removes the given allocation row.
     * $rowid : unique id of allocation row
     */
    function deleteAllocationRow($rowid) {
        if (isset($this->allocationRows[$rowid])) {
            unset($this->allocationRows[$rowid]);
        }
    }

    /**
     * Updates the name, resource fields on the given allocation row.
     * $rowid : unique id of allocation row
     * $allocationName : name of guest
     * $resourceId : valid resource id (can be parent)
     */
    function updateAllocationRow($rowid, $allocationName, $resourceId) {
        if (isset($this->allocationRows[$rowid])) {
            $this->allocationRows[$rowid]->name = $allocationName;
            
            // save and assign resources
            if($this->allocationRows[$rowid]->resourceId != $resourceId) {
                $this->allocationRows[$rowid]->resourceId = $resourceId;
                
                // perform the individual assignments to beds if a parent resource id is specified
                // validation (on availability) will be done on save
                $existingAllocations = array();   // array of all rows excluding the one being edited
                foreach ($this->allocationRows as $k => $v) {
                    if($k != $rowid) {
                        $existingAllocations[$k] = $v;
                    }
                }
                
                $this->allocationStrategy->assignResourcesForAllocations(
                    array($this->allocationRows[$rowid]), $existingAllocations);
            }
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
            if(false === $row->isExistsBooking()) {
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
    
        // we need to delete any allocations that have been removed since we last saved
        $oldAllocationRows = AllocationDBO::fetchAllocationRowsForBookingId($bookingId, $this->resourceMap, false);

        // existing (possibly changed) allocations, keep in a array indexed by id
        $allocationRowsById = array();  // indexed by id where id > 0; we need to update these rows if they have changed
        foreach ($this->allocationRows as $ar) {
            if ($ar->id > 0) {
                $allocationRowsById[$ar->id] = $ar;
            }
        }

        // diff existing records with the ones we want to save
        // if it exists in the old but not in the new, delete it
error_log("allocation table.save() : ".var_export(array(array_keys($oldAllocationRows), array_keys($allocationRowsById)), true));
        $allocationRowsToRemove = array_diff_key($oldAllocationRows, $allocationRowsById);
        foreach ($allocationRowsToRemove as $allocId => $ar) {
            AllocationDBO::deleteAllocation($mysqli, $allocId);
        }
        
        $failedAllocation = false;
        foreach ($this->allocationRows as $alloc) {
            $alloc_id = $alloc->save($mysqli, $bookingId);

            if( ! $alloc->isAvailable) {
                $failedAllocation = true;
            }
        }
        
        // report business error if demand > supply
        if ($failedAllocation) {
            throw new AllocationException("One or more allocations did not have sufficient availability");
        }
    }
    
    /**
     * Loads current allocations from the db and resets the min/max dates to default.
     * $bookingId : booking id for this allocation
     */
    function load($bookingId) {
        $this->allocationRows = AllocationDBO::fetchAllocationRowsForBookingId($bookingId, $this->resourceMap);
        $this->setDefaultMinMaxDates();
    }
    
    /**
     * Adds this allocation table to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element of the xml tree
        $xmlRoot = $parentElement->appendChild($domtree->createElement('allocations'));
    
        if($this->showMinDate != null) {
            $xmlRoot->appendChild($domtree->createElement('showMinDate', $this->showMinDate->format('d.m.Y')));
        }
        if($this->showMaxDate != null) {
            $xmlRoot->appendChild($domtree->createElement('showMaxDate', $this->showMaxDate->format('d.m.Y')));
        }
        if($this->editingRowId != null) {
            $xmlRoot->appendChild($domtree->createElement('editingRowId', $this->editingRowId));
        }

        foreach ($this->allocationRows as $allocation) {
            $allocation->showMinDate = $this->showMinDate;
            $allocation->showMaxDate = $this->showMaxDate;
            $allocation->addSelfToDocument($domtree, $xmlRoot);
        }

        // resources are required for dropdown when editing an allocation row
        $resourcesRoot = $xmlRoot->appendChild($domtree->createElement('resources'));
        foreach ($this->resourceMap as $res) {
            $resourceRow = $domtree->createElement('resource');
            $resourceRow->appendChild($domtree->createElement('id', $res->resource_id));
            $resourceRow->appendChild($domtree->createElement('name', $res->name));
            $resourceRow->appendChild($domtree->createElement('level', $res->lvl));
            $resourcesRoot->appendChild($resourceRow);
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
    }
    
    /** 
      Generates the following xml:
        <allocations>
            <bookingName>Megan</bookingName>
            <showMinDate>25.08.2012</showMinDate>
            <showMaxDate>04.09.2012</showMaxDate>
            <allocation>...</allocation>
            <allocation>...</allocation>
            <resources>
                ...
            </resources>
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
        $this->addSelfToDocument($domtree, $domtree);
error_log($domtree->saveXML());
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/allocation_table.xsl';
    }
}

?>