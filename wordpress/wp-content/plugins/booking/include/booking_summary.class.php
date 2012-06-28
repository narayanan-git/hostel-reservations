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

    function BookingSummary($id = 0, $firstname = null, $lastname = null, $referrer = null, $createdBy = null, $createdDate = null) {
        $this->id = $id;
        $this->firstname = $firstname;
        $this->lastname = $lastname;
        $this->referrer = $referrer;
        $this->createdBy = $createdBy;
        $this->createdDate = $createdDate;
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