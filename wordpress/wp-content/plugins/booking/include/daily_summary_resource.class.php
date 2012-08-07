<?php

/**
 * Daily Summary nested resource object.
 */
class DailySummaryResource {

    var $id;
    var $name;
    var $level;
    var $path;
    var $parentId;
    var $checkedInCount = 0;
    var $checkedInRemaining = 0;
    var $checkedOutCount = 0;
    var $checkedOutRemaining = 0;
    
    private $childResources = array(); // array() of DailySummaryResource
    
    function DailySummaryResource($id = 0, $name = null, $level = null, $path = null, $parentId = null) {
        $this->id = $id;
        $this->name = $name;
        $this->level = $level;
        $this->path = $path;
        $this->parentId = $parentId;
    }

    /**
     * Adds child resource to this object's list of children.
     * $childResource : DailySummaryResource to add as child
     */
    function addChildResource($childResource) {
        $this->childResources[] = $childResource;
    }
    
    /**
     * Adds the "checkin" attributes to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addCheckInsToDocument($domtree, $parentElement) {
        // create the root element for this object
        $checkinRoot = $domtree->createElement('checkin');
        $parentElement = $parentElement->appendChild($checkinRoot);
        
        $attrArrived = $domtree->createAttribute('arrived');
        $attrArrived->value = $this->checkedInCount;
        $parentElement->appendChild($attrArrived);

        $attrRemaining = $domtree->createAttribute('remaining');
        $attrRemaining->value = $this->checkedInRemaining;
        $parentElement->appendChild($attrRemaining);

        $parentElement->appendChild($domtree->createElement('id', $this->id));
        $parentElement->appendChild($domtree->createElement('caption', $this->name));
    
        foreach ($this->childResources as $res) {
            $res->addCheckInsToDocument($domtree, $parentElement);
        }
    }

    /**
     * Adds the "checkout" attributes to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addCheckOutsToDocument($domtree, $parentElement) {
        // create the root element for this object
        $checkoutRoot = $domtree->createElement('checkout');
        $parentElement = $parentElement->appendChild($checkoutRoot);
        
        $attrDeparted = $domtree->createAttribute('departed');
        $attrDeparted->value = $this->checkedOutCount;
        $parentElement->appendChild($attrDeparted);

        $attrRemaining = $domtree->createAttribute('remaining');
        $attrRemaining->value = $this->checkedOutRemaining;
        $parentElement->appendChild($attrRemaining);

        $parentElement->appendChild($domtree->createElement('id', $this->id));
        $parentElement->appendChild($domtree->createElement('caption', $this->name));
    
        foreach ($this->childResources as $res) {
            $res->addCheckOutsToDocument($domtree, $parentElement);
        }
    }
}

?>