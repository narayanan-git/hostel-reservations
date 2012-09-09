<?php

/**
 * Encapsulates and renders a view of selected bookings.
 */
class BookingView extends XslTransform {
    private $bookings = array();  // array of BookingSummary
    var $minDate;   // earliest date (DateTime)
    var $maxDate;   // latest date (DateTime)
    var $resourceId; // match this resource, null for any
    var $dateMatchType;  // one of 'checkin', 'reserved', 'creation'
    var $status;     // one of 'reserved', 'checkedin', 'checkedout', 'cancelled', 'all'
    var $matchName;  // (partial) name to match (not case sensitive), null for any
        
    /**
     * Default constructor.
     * $minDate : earliest DateTime of booking to search
     * $maxDate : latest DateTime of booking to search
     * $dateMatchType : one of 'checkin', 'reserved', 'creation' (default 'checkin')
     * $status : one of 'reserved', 'checkedin', 'checkedout', 'cancelled', 'all' (default 'all')
     * $resourceId : id of resource to match (if parent resource, searches all children; optional)
     * $matchName : (partial) name to match with * as wildcard (not case sensitive; optional)
     */
    function BookingView($minDate = null, $maxDate = null, 
            $dateMatchType = 'checkin', $status = 'all', $resourceId = null, $matchName = null) {
        // default date to today
        $this->minDate = $minDate == null ? new DateTime() : $minDate;
        $this->maxDate = $maxDate == null ? new DateTime() : $maxDate;
        $this->dateMatchType = $dateMatchType;
        $this->resourceId = $resourceId;
        $this->status = $status;
        $this->matchName = $matchName;
    }
    
    /**
     * Runs the search using the current criterion.
     * Saves the result in the current object.
     */
    function doSearch() {
        $this->bookings = BookingDBO::getBookingsForDateRange(
                $this->minDate, $this->maxDate, $this->dateMatchType,
                $this->resourceId, 
                $this->status, 
                $this->matchName);
    }

    /**
     * Adds this BookingView to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $xmlRoot = $parentElement->appendChild($domtree->createElement('bookingview'));

        // search criteria
        $filterRoot = $xmlRoot->appendChild($domtree->createElement('filter'));
        $filterRoot->appendChild($domtree->createElement('bookingmindate', $this->minDate->format('Y-m-d')));
        $filterRoot->appendChild($domtree->createElement('bookingmaxdate', $this->maxDate->format('Y-m-d')));
        $filterRoot->appendChild($domtree->createElement('datetype', $this->dateMatchType));
        $filterRoot->appendChild($domtree->createElement('resourceId', $this->resourceId));
        $filterRoot->appendChild($domtree->createElement('status', $this->status));
        $filterRoot->appendChild($domtree->createElement('matchname', $this->matchName));
        
        foreach ($this->bookings as $book) {
            $book->addSelfToDocument($domtree, $xmlRoot);
        }
    }

    /** 
      Generates the following xml:
        <view>
            <filter>
                <mindate>2012-06-21</mindate>
                <maxdate>2012-06-28</maxdate>
                <datetype>checkin</datetype>
                <resourceId>1</resourceId>
                <status>checkedin</status>
                <matchname>megan</matchname>
            </filter>
            <booking>
                <id>1</id>
                <firstname>john</firstname>
                <lastname>smith</lastname>
                <referrer>Hostelworld</referrer>
                <createddate>03-Jun-2012 Wed 14:39</createddate>
                <createdby>admin</createdby>
                <details>requested bottom bunk</details>
                <guests>
                    <guest>john smith</guest>
                    <guest>amanda knox</guest>
                </guests>
                <statuses>
                    <status>reserved</status>
                    <status>checkedin</status>
                </statuses>
                <resources>
                    <resource>Room 12</resource>
                    <resource>Room 14</resource>
                </resources>
                <dates>
                    <daterange>
                        <from>July 5, 2012</from>
                        <to>July 20, 2012</to>
                    </daterange>
                    <date>July 24, 2012</date>
                    <daterange>
                        <from>August 2, 2012</from>
                        <to>August 6, 2012</to>
                    </daterange>
                </dates>
            </booking>
            <booking>
                ...
            </booking>
        </view>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/booking_view.xsl';
    }
}

?>