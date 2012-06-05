<?php

/**
 * Encapsulates and renders a table containing all allocations for a booking.
 */
class AllocationTable {
    var $bookingName;   // person who made the booking
    var $showMinDate;   // minimum date to show on the table
    var $showMaxDate;   // maximum date to show on the table
    private $allocationRows = array();  // array of AllocationRow
    
    function AllocationTable($bookingName) {
        $this->bookingName = $bookingName;
    }

    function addAllocation($numVisitors, $gender, $resourceId) {
        for($i = 0; $i < $numVisitors; $i++) {
            $this->allocationRows[] = new AllocationRow($this->bookingName.'-'.(sizeof($this->allocationRows)+1), $gender, $resourceId);
        }
    }
    
    /**
     * Calculates total payment by summing all allocation rows.
     * Returns: numeric value
     */
    function getTotalPayment() {
        $result = 0;
        foreach ($this->allocationRows as $allocation) {
            $result += $allocation->getTotalPayment();
        }
        return $result;
    }
    
    /** 
      Generates the following xml:
        <allocations total="49.50">
            <bookingName>Megan</bookingName>
            <showMinDate>15.08.2012</showMinDate>
            <showMaxDate>25.08.2012</showMaxDate>
            <allocation>...</allocation>
            <allocation>...</allocation>
        </allocations>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
    
        // create the root element of the xml tree
        $xmlRoot = $domtree->createElement('allocations');
        $xmlRoot = $domtree->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('bookingName', $this->bookingName));
        $xmlRoot->appendChild($domtree->createElement('showMinDate', $this->showMinDate));
        $xmlRoot->appendChild($domtree->createElement('showMaxDate', $this->showMaxDate));

        $attrTotal = $domtree->createAttribute('total');
        $attrTotal->value = $this->getTotalPayment();
        $xmlRoot->appendChild($attrTotal);
        foreach ($this->allocationRows as $allocation) {
            $allocation->addSelfToDocument($domtree, $xmlRoot);
        }
        return $domtree->saveXML();
    }
    
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/allocation_table.xsl');
        
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