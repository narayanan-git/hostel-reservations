<?php

/**
 * Summary for a single booking.
 */
class BookingSummary extends XslTransform {
    var $id;  
    var $firstname;
    var $lastname;
    var $referrer;
    var $createdBy;
    var $createdDate;
    var $guests;  // array of String (one for each guest name) for this booking
    var $statuses; // unique array of String (one for each status) for this booking
    var $resources; // unique array of String (one for each resource) for this booking
    var $bookingDates; // unique array of DateTime (one for each day an allocation exists)
    var $comments; // array of String (user comments) for this booking
    var $isCheckoutAllowed;  // boolean : true if checkout can be applied to booking, false otherwise

    function BookingSummary($id = 0, $firstname = null, $lastname = null, $referrer = null, $createdBy = null, $createdDate = null) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->referrer = $referrer;
        $this->createdBy = $createdBy;
        $this->createdDate = $createdDate;
        $this->guests = array();
        $this->statuses = array();
        $this->resources = array();
        $this->bookingDates = array();
        $this->comments = array();
        $this->isCheckoutAllowed = false;
    }
    
    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $xmlRoot = $parentElement->appendChild($domtree->createElement('booking'));
        $xmlRoot->appendChild($domtree->createElement('homeurl', home_url()));
        $xmlRoot->appendChild($domtree->createElement('editbooking_url', home_url()."/".get_option('hbo_editbooking_url')));

        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('firstname', $this->firstname));
        $xmlRoot->appendChild($domtree->createElement('lastname', $this->lastname));
        $xmlRoot->appendChild($domtree->createElement('referrer', $this->referrer));
        $xmlRoot->appendChild($domtree->createElement('createdBy', $this->createdBy));
        $xmlRoot->appendChild($domtree->createElement('createdDate', 
            $this->createdDate == null ? null : $this->createdDate->format('D, d M Y g:i a')));
            
        $guestRoot = $xmlRoot->appendChild($domtree->createElement('guests'));
        foreach($this->guests as $guest) {
            $guestRoot->appendChild($domtree->createElement('guest', $guest));
        }

        $statusesRoot = $xmlRoot->appendChild($domtree->createElement('statuses'));
        foreach($this->statuses as $status) {
            $statusesRoot->appendChild($domtree->createElement('status', $status));
        }

        $resourcesRoot = $xmlRoot->appendChild($domtree->createElement('resources'));
        foreach($this->resources as $resource) {
            $resourcesRoot->appendChild($domtree->createElement('resource', $resource));
        }

        $commentsRoot = $xmlRoot->appendChild($domtree->createElement('comments'));
        foreach($this->comments as $comment) {
            $comment->addSelfToDocument($domtree, $commentsRoot);
        }

        $datesRoot = $xmlRoot->appendChild($domtree->createElement('dates'));
        $this->appendDatesToXmlElement($domtree, $datesRoot);

        if ($this->isCheckoutAllowed) {
            $xmlRoot->appendChild($domtree->createElement('allowCheckout', 'true'));
        }
    }
    
    /**
     * Adds the booking dates to the DOMDocument/XMLElement specified.
     * Ranges are added as <daterange> elements and single dates are added as <date> elements.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function appendDatesToXmlElement($domtree, $parentElement) {
        // dates should be in order, so iterate over dates
        $startDate = null;
        $currentDate = null;
        foreach($this->bookingDates as $dt) {
            // we are looking at the first element
            if($startDate == null) {
                $startDate = $dt;
                $currentDate = $dt;

            } else {
                // if we are continuing the current range
                $datediff = $currentDate->diff($dt);
                if($datediff->d == 1) {
                    $currentDate = $dt;
    
                // if we are jumping more than 1 day, end the current range
                } else if($datediff->d > 1) {
                
                    // not a range, just add the single date
                    if ($startDate == $currentDate) {
                        $parentElement->appendChild($domtree->createElement('date', $startDate->format('F d, Y')));
    
                    } else { // add the range
                        $rangeElement = $parentElement->appendChild($domtree->createElement('daterange'));
                        $rangeElement->appendChild($domtree->createElement('from', $startDate->format('F d, Y')));
                        $rangeElement->appendChild($domtree->createElement('to', $currentDate->format('F d, Y')));
                    }
                    // reset range
                    $startDate = $dt;
                    $currentDate = $dt;
                }
            }
        }
        
        // we missed the last element in the list, so add it now
        if (sizeof($this->bookingDates) > 0) {
            // not a range, just add the single date
            if ($startDate == $currentDate) {
                $parentElement->appendChild($domtree->createElement('date', $startDate->format('F d, Y')));
    
            } else { // add the range
                $rangeElement = $parentElement->appendChild($domtree->createElement('daterange'));
                $rangeElement->appendChild($domtree->createElement('from', $startDate->format('F d, Y')));
                $rangeElement->appendChild($domtree->createElement('to', $currentDate->format('F d, Y')));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <booking>
            <homeurl>http://localhost:16571</homeurl>
            <editbooking_url>http://localhost:16571/edit-booking</editbooking_url>
            <id>3</id>
            <firstname>Megan</firstname>
            <lastname>Female</lastname>
            <referrer>Hostelworld</referrer>
            <createdBy>admin</createdBy>
            <createdDate>Tue, 12 Jun 2012 04:29 am</createdDate>
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
            <comments>
                <comment>...</comment>
                ...
            </comments>
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
            <allowCheckout>true</allowCheckout>
        </booking>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/booking_summary.xsl';
    }
}

?>