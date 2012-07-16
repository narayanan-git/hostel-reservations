<?php

/**
 * Container for both BookingView and AllocationView.
 */
class BookingAllocationView extends XslTransform {

    var $bookingView;      // BookingView
    var $allocationView;   // AllocationView
    
    function BookingAllocationView() {
        $this->bookingView = new BookingView();
        $this->allocationView = new AllocationView();
    }
    
    /** 
      Generates the following xml:
        <view>
            <bookingview>
                ...
            </bookingview>
            <allocationview>
                ...
            </allocationview>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));

        $this->bookingView->addSelfToDocument($domtree, $xmlRoot);
        $this->allocationView->addSelfToDocument($domtree, $xmlRoot);

        return $domtree->saveXML();
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/booking_allocation_view.xsl';
    }
}

?>