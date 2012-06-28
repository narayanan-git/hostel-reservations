<?php

/**
 * Encapsulates and renders a view of selected bookings.
 */
class BookingsView {
    private $bookings = array();  // array of BookingSummary
    var $minDate;   // earliest date (DateTime)
    var $maxDate;   // latest date (DateTime)
    var $resourceId; // match this resource, null for any
    var $dateMatchType;  // one of 'checkin', 'reserved', 'creation'
    var $status;     // one of 'reserved', 'checkedin', 'checkedout', 'cancelled', null for any
    var $matchName;  // (partial) name to match (not case sensitive), null for any
    
    function BookingsView() {
        // default date to today
        $this->minDate = new DateTime();
        $this->maxDate = new DateTime();
        $this->dateMatchType = 'checkin';
        $this->resourceId = null;
        $this->status = null;
        $this->matchName = null;
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
                    <guest>
                        <name>john smith</name>
                        <status>reserved</status>
                        <gender>male</gender>
                    </guest>
                    <guest>
                        <name>amanda innis</name>
                        <status>checkedin</status>
                        <gender>female</gender>
                    </guest>
                </guests>
                <dates>
                    <daterange>July 5, 2012 - July 20, 2012</daterange>
                    <daterange>July 24, 2012</daterange>
                    <daterange>August 2, 2012 - August 6, 2012</daterange>
                </dates>
                <resources>
                    <name>Room 18</name>
                    <name>Room 19</name>
                </resources>
            </booking>
            <booking>
                ...
            </booking>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));

        // search criteria
        $filterRoot = $xmlRoot->appendChild($domtree->createElement('filter'));
        $filterRoot->appendChild($domtree->createElement('mindate', $this->minDate->format('Y-m-d')));
        $filterRoot->appendChild($domtree->createElement('maxdate', $this->maxDate->format('Y-m-d')));
        $filterRoot->appendChild($domtree->createElement('datetype', $this->dateMatchType));
        $filterRoot->appendChild($domtree->createElement('resourceId', $this->resourceId));
        $filterRoot->appendChild($domtree->createElement('status', $this->status));
        $filterRoot->appendChild($domtree->createElement('matchname', $this->matchName));
        
        foreach ($this->bookings as $book) {
            $book->addSelfToDocument($domtree, $xmlRoot);
        }

        return $domtree->saveXML();
    }
    
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/bookings_view.xsl');
        
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