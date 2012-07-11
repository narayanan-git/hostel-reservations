<?php

/**
 * Summary for a single booking.
 */
class BookingSummary {
    var $id;  
    var $firstname;
    var $lastname;
    var $referrer;
    var $createdBy;
    var $createdDate;
    var $guests;  // array of String (one for each guest name) for this booking
    var $statuses; // unique array of String (one for each status) for this booking
    var $resources; // unique array of String (one for each resource) for this booking

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
    }
    
    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $xmlRoot = $domtree->createElement('booking');
        $xmlRoot = $parentElement->appendChild($xmlRoot);

        $xmlRoot->appendChild($domtree->createElement('id', $this->id));
        $xmlRoot->appendChild($domtree->createElement('firstname', $this->firstname));
        $xmlRoot->appendChild($domtree->createElement('lastname', $this->lastname));
        $xmlRoot->appendChild($domtree->createElement('referrer', $this->referrer));
        $xmlRoot->appendChild($domtree->createElement('createdBy', $this->createdBy));
        $xmlRoot->appendChild($domtree->createElement('createdDate', 
            $this->createdDate == null ? null : $this->createdDate->format('D, d M Y H:i a')));
            
        $guestRoot = $xmlRoot->appendChild($domtree->createElement('guests'));
        foreach($this->guests as $guest) {
            $guestRoot->appendChild($domtree->createElement('guest', $guest));
        }
/*
        $statusesRoot = $xmlRoot->appendChild($domtree->createElement('statuses'));
        foreach($this->statuses as $status) {
            $statusesRoot->appendChild($domtree->createElement('status', $status));
        }

        $resourcesRoot = $xmlRoot->appendChild($domtree->createElement('resources'));
        foreach($this->resources as $resource) {
            $resourcesRoot->appendChild($domtree->createElement('resource', $resource));
        }
*/
    }
    
    /** 
      Generates the following xml:
        <booking>
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
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
}

?>