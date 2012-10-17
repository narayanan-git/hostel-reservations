<?php

/**
 * Renders one cell across a calendar view of an allocation.
 */
class AllocationCell {
    var $id;  // allocation id
    var $bookingId;  // associated booking id
    var $name;
    var $status;  // booking date status (e.g. paid, hours, reserved, etc..)
    var $gender;
    var $renderState;  // one of 'rounded_left', 'rounded_right', 'rounded_both', 'rounded_neither'
    var $checkedOut;   // checked out status : null or boolean

    function AllocationCell($id = 0, $bookingId = null, $name = null, $gender = null, $status = 'reserved', $renderState = null, $checkedOut = null) {
        $this->id = $id;
        $this->bookingId = $bookingId;
        $this->name = $name;
        $this->gender = $gender;
        $this->status = $status;
        $this->renderState = $renderState;
        if ($checkedOut != null) {
            $this->checkedOut = $checkedOut;
        }
    }
    
    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $xmlRoot = $domtree->createElement('allocationcell');
        $xmlRoot = $parentElement->appendChild($xmlRoot);

        // show content only for valid ids
        if($this->id > 0) {
            $xmlRoot->appendChild($domtree->createElement('id', $this->id));
            $xmlRoot->appendChild($domtree->createElement('bookingid', $this->bookingId));
            $xmlRoot->appendChild($domtree->createElement('name', $this->name));
            $xmlRoot->appendChild($domtree->createElement('gender', $this->gender));
            $xmlRoot->appendChild($domtree->createElement('status', $this->status));
            $xmlRoot->appendChild($domtree->createElement('render', $this->renderState));
            if ($this->checkedOut != null) {
                $xmlRoot->appendChild($domtree->createElement('checkedout', $this->checkedOut ? 'true' : 'false'));
            }
        }
    }
    
    /** 
      Generates the following xml:
        <allocationcell>
            <id>3</id>
            <bookingid>1</bookingid>
            <name>Megan-1</name>
            <gender>Female</gender>
            <status>paid</status>
            <render>rounded_left</render>
            <checkedout>false</checkedout>
        </allocationcell>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
}

?>