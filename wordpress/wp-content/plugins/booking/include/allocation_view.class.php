<?php

/**
 * Encapsulates and renders a view of selected allocations for a given date range.
 */
class AllocationView extends XslTransform {
    private $bookingResources = array();  // array of BookingResource
    var $showMinDate;   // earliest date to show (DateTime)
    var $showMaxDate;   // latest date to show (DateTime)
    
    /**
     * Default constructor.
     * $startDate : begin date to show (inclusive; optional DateTime)
     * $endDate : end date to show (inclusive; optional DateTime)
     */
    function AllocationView($startDate = null, $endDate = null) {
    
        // default dates to 2-week range from 2-days ago
        if ($startDate == null) {
            $this->showMinDate = new DateTime();
            $this->showMinDate->sub(new DateInterval('P2D'));
        } else {
            $this->showMinDate = $startDate;
        }
        
        if ($endDate == null) {
            $this->showMaxDate = new DateTime();
            $this->showMaxDate->add(new DateInterval('P12D'));
        } else {
            $this->showMaxDate = $endDate;
        }
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
     * Goes through the booking resources and updates the 'unpaid' flag to true
     * where a reservation exists for the given date and a "paid" status on a 
     * previous date for the same allocation.
     * $selectedDate : DateTime of date to check
     */
    function markUnpaidResources($selectedDate) {
        $resourceIds = ResourceDBO::fetchResourceIdsPastDue($selectedDate);

        if (sizeof($resourceIds) > 0) {
            foreach ($this->bookingResources as $br) {
                $br->markUnpaidResources($resourceIds);
            }
        }
    }
    
    /**
     * Toggles the checkout state of the given allocation and position in allocationCells.
     * $allocationId : id of allocation
     * $posn : position within allocationCells to toggle (multiple checkout dates may be possible for any given allocation)
     */
    function toggleCheckoutOnBookingDate($allocationId, $posn) {
    
        // find out the date that was toggled by adding the offset to minDate
        $checkoutDate = clone $this->showMinDate;
        $checkoutDate->add(new DateInterval('P'.$posn.'D'));

        AllocationDBO::toggleCheckoutOnBookingDate($allocationId, $checkoutDate);
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
        $xmlRoot->appendChild($domtree->createElement('homeurl', home_url()));
        $xmlRoot->appendChild($domtree->createElement('editbooking_url', home_url()."/".get_option('hbo_editbooking_url')));

        // search criteria
        $filterRoot = $xmlRoot->appendChild($domtree->createElement('filter'));
        $filterRoot->appendChild($domtree->createElement('allocationmindate', $this->showMinDate->format('Y-m-d')));
        $filterRoot->appendChild($domtree->createElement('allocationmaxdate', $this->showMaxDate->format('Y-m-d')));
        
        foreach ($this->bookingResources as $book) {
            $book->addSelfToDocument($domtree, $xmlRoot);
        }
        
        self::addDateHeadersToDocument($domtree, $xmlRoot, $this->showMinDate, $this->showMaxDate);

        // count total number of free beds
        $totalfreebeds = array();
        foreach ($this->bookingResources as $book) {
            foreach ($book->freebeds as $i => $fb) {
                if (isset($totalfreebeds[$i])) {
                    $totalfreebeds[$i] += $fb;
                } else {
                    $totalfreebeds[$i] = $fb;
                }
            }
        }
        
        $totals = $xmlRoot->appendChild($domtree->createElement('totals'));
        $freebeds = $totals->appendChild($domtree->createElement('freebeds'));
        foreach ($totalfreebeds as $tfb) {
            $freebeds->appendChild($domtree->createElement('freebed', $tfb));
        }
    }
    
    /**
     * Adds the <dateheaders> element to the DOMDocument/XMLElement specified.
     * <dateheaders>
           <header>July - August</header>
           <datecol>
               <date>27</date>
               <day>Fri</day>
           </datecol>
           <datecol>
               <date>28</date>
               <day>Sat</day>
           </datecol>
           ...
     * </dateheaders>
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     * $minDate : first date inclusive (DateTime)
     * $maxDate : last date inclusive (DateTime)
     */
    static function addDateHeadersToDocument($domtree, $parentElement, $minDate, $maxDate) {
        // build dateheaders to be used to display availability table
        if($minDate != null && $maxDate != null) {
            $dateHeaders = $parentElement->appendChild($domtree->createElement('dateheaders'));
            
            // if spanning more than one month, print out both months
            if($minDate->format('F') !== $maxDate->format('F')) {
                $dateHeaders->appendChild($domtree->createElement('header', $minDate->format('F') . ' - ' . $maxDate->format('F')));
            } else {
                $dateHeaders->appendChild($domtree->createElement('header', $minDate->format('F')));
            }
            
            $dt = clone $minDate;
            while ($dt <= $maxDate) {
                $dateElem = $dateHeaders->appendChild($domtree->createElement('datecol'));
                $dateElem->appendChild($domtree->createElement('date', $dt->format('d')));
                $dateElem->appendChild($domtree->createElement('day', $dt->format('D')));
                $dt->add(new DateInterval('P1D'));  // increment by day
            }
        }
    }

    /** 
      Generates the following xml:
        <allocationview>
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
            
            <totals>
                <freebeds>
                    <freebed>30</freebed>
                    <freebed>40</freebed>
                    ....
                </freebeds>
            </totals>
        </allocationview>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/allocation_view.xsl';
    }
}

?>