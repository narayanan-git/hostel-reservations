<?php

/**
 * Subview of AllocationView that renders a single resource for a given date range.
 */
class AllocationViewResource extends XslTransform {
    var $bookingResource;  // BookingResource
    var $resourceId;    // id of (parent) resource for this view
    var $showMinDate;   // earliest date to show (DateTime)
    var $showMaxDate;   // latest date to show (DateTime)
    
    /**
     * Default constructor.
     * $resourceId : id of resource to render
     * $startDate : begin date to show (inclusive)
     * $endDate : end date to show (inclusive)
     */
    function AllocationViewResource($resourceId, $startDate, $endDate) {
        $this->resourceId = $resourceId;
        $this->showMinDate = $startDate;
        $this->showMaxDate = $endDate;
        $this->bookingResource = null;
    }
    
    /**
     * Runs the search by allocation grouped by resource.
     * Saves the result in the current object.
     */
    function doSearch() {
        $bookingResources = AllocationDBO::getAllocationsByResourceForDateRange(
                $this->showMinDate, $this->showMaxDate, $this->resourceId, 
                null /* status */, 
                null /* name */);
        if (sizeof($bookingResources) != 1) {
            error_log("Unexpected number of items in AllocationViewResource::doSearch() ");
foreach ($bookingResources as $br) {
   error_log("Found BookingResource $br->resourceId");
}
        }
        
        if (sizeof($bookingResources) >= 0) {
            $this->bookingResource = array_shift($bookingResources);
        } else {
            $this->bookingResource = null;
        }
    }
    
    /**
     * Adds this AllocationView to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $xmlRoot = $parentElement->appendChild($domtree->createElement('allocationview'));

        // search criteria
        $filterRoot = $xmlRoot->appendChild($domtree->createElement('filter'));
        $filterRoot->appendChild($domtree->createElement('allocationmindate', $this->showMinDate->format('Y-m-d')));
        $filterRoot->appendChild($domtree->createElement('allocationmaxdate', $this->showMaxDate->format('Y-m-d')));
        
        if ($this->bookingResource != null) {
            $this->bookingResource->addSelfToDocument($domtree, $xmlRoot);
        }
        
        AllocationView::addDateHeadersToDocument($domtree, $xmlRoot, $this->showMinDate, $this->showMaxDate);
    }

    /** 
      Generates the following xml:
        <allocationview>
            <filter>
                <allocationmindate>2012-06-21</allocationmindate>
                <allocationmaxdate>2012-06-28</allocationmaxdate>
            </filter>
            <resource>
                <id>4</id>
                <name>Room 10</name>
                <type>room</type>
                <resource>
                    <id>5</id>
                    <name>Bed A</name>
                    <type>bed</type>
                    <cells> <!-- cells comprises one row on the allocation table -->
                        <allocationcell/>
                        <allocationcell>
                            <id>1</id>
                            <name>Megan-1</name>
                            <gender>Female</gender>
                            <status>paid</status>
                            <render>rounded_both</render>
                        </allocationcell>
                        <allocationcell/>
                        <allocationcell>
                            <id>2</id>
                            <name>Romeo-1</name>
                            <gender>Female</gender>
                            <status>reserved</status>
                            <render>rounded_left</render>
                        <allocationcell>
                    </cells>
                </resource>
                <resource>
                    ...
                </resource>
            </resource>
            <resource>
                ...
            </resource>
            
            <dateheaders>
                ...
            </dateheaders>
        </allocationview>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
error_log("allocation view resource: ".$domtree->saveXML());
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/allocation_view_resource.xsl';
    }
}

?>