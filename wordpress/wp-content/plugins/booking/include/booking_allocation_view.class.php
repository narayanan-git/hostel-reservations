<?php

/**
 * Container for both BookingView and AllocationView.
 */
class BookingAllocationView {

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
     * Generates HTML via XSLT.
     */
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/booking_allocation_view.xsl');
        
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