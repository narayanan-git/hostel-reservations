<?php

/**
 * Encapsulates and renders a view of selected allocations for a given date range.
 */
class AllocationView {
    private $bookingResources = array();  // array of BookingResource
    var $showMinDate;   // earliest date to show (DateTime)
    var $showMaxDate;   // latest date to show (DateTime)
    
    function AllocationView() {
        // default dates to 2-week range from 2-days ago
        $this->showMinDate = new DateTime();
        $this->showMinDate->sub(new DateInterval('P2D'));
        $this->showMaxDate = new DateTime();
        $this->showMaxDate->add(new DateInterval('P12D'));
    }
    
    /**
     * Runs the search by allocation grouped by resource.
     * Saves the result in the current object.
     */
    function doSearch() {
        $this->bookingResources = AllocationDBO::getAllocationsByResourceForDateRange(
                $this->showMinDate, $this->showMaxDate, null /* resource id */, 
                null /* status */, 
                null /* name */);
    }

    /** 
      Generates the following xml:
        <view>
            <filter>
                <allocationmindate>2012-06-21</allocationmindate>
                <allocationmaxdate>2012-06-28</allocationmaxdate>
            </filter>
            <resource>
                <id>1</id>
                <name>Hostelworld 10 Bed Mixed Dorm</name>
                <resource>
                    <id>4</id>
                    <name>Room 10</name>
                    <type>room</type>
                    <resource>
                        <id>5</id>
                        <name>Bed A</name>
                        <type>bed</type>
                        <cells> <!-- cells comprises one row on the allocation table -->
                            <allocationcell span="1"/>
                            <allocationcell span="4">
                                <id>1</id>
                                <name>Megan-1</name>
                                <gender>Female</gender>
                                <status>checkedin</status>
                            </allocationcell>
                            <allocationcell span="2"/>
                            <allocationcell span="3">
                                <id>2</id>
                                <name>Romeo-1</name>
                                <gender>Female</gender>
                                <status>checkedin</status>
                            <allocationcell>
                        </cells>
                    </resource>
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
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));

        // search criteria
        $filterRoot = $xmlRoot->appendChild($domtree->createElement('filter'));
        $filterRoot->appendChild($domtree->createElement('allocationmindate', $this->showMinDate->format('Y-m-d')));
        $filterRoot->appendChild($domtree->createElement('allocationmaxdate', $this->showMaxDate->format('Y-m-d')));
        
        foreach ($this->bookingResources as $book) {
            $book->addSelfToDocument($domtree, $xmlRoot);
        }

        // build dateheaders to be used to display availability table
        if($this->showMinDate != null && $this->showMaxDate != null) {
            $dateHeaders = $xmlRoot->appendChild($domtree->createElement('dateheaders'));
            
            // if spanning more than one month, print out both months
            if($this->showMinDate->format('F') !== $this->showMaxDate->format('F')) {
                $dateHeaders->appendChild($domtree->createElement('header', $this->showMinDate->format('F') . ' - ' . $this->showMaxDate->format('F')));
            } else {
                $dateHeaders->appendChild($domtree->createElement('header', $this->showMinDate->format('F')));
            }
            
            $dt = clone $this->showMinDate;
            while ($dt <= $this->showMaxDate) {
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
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/allocation_view.xsl');
        
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