<?php

/**
 * Encapsulates one row in the allocation table (one bed for one person)
 * and renders it.
 */
class AllocationRow {
    var $name;
    var $gender;
    var $resource;
    private $bookingDatePayment = array();  // key = booking date, value = payment amount

    function AllocationRow($name, $gender, $resource) {
        $this->name = $name;
        $this->gender = $gender;
        $this->resource = $resource;
    }
    
    function addPaymentForDate($dt, $payment) {
        $this->bookingDatePayment[$dt] = $payment;
    }
    
    /**
     * Calculates total payment by summing bookingDatePayment
     * Returns: numeric value
     */
    function getTotalPayment() {
        $result = 0;
        foreach ($this->bookingDatePayment as $bookingDate => $payment) {
            $result += $payment;
        }
        return $result;
    }

    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $xmlRoot = $domtree->createElement('allocation');
        $xmlRoot = $parentElement->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('name', $this->name));
        $xmlRoot->appendChild($domtree->createElement('gender', $this->gender));
        $xmlRoot->appendChild($domtree->createElement('resource', $this->resource));

        $dateRow = $domtree->createElement('dates');
        $attrTotal = $domtree->createAttribute('total');
        $attrTotal->value = $this->getTotalPayment();
        $dateRow->appendChild($attrTotal);
        foreach ($this->bookingDatePayment as $bookingDate => $payment) {
            $dateElem = $dateRow->appendChild($domtree->createElement('date', $bookingDate));
            $attrPayment = $domtree->createAttribute('payment');
            $attrPayment->value = $payment;
            $dateElem->appendChild($attrPayment);
        }
        $xmlRoot->appendChild($dateRow);
    }
    
    /** 
      Generates the following xml:
        <allocation>
            <name>Megan-1</name>
            <gender>Female</gender>
            <resource>Bed A</resource>
            <dates total="24.90">
                <date payment="12.95" state="checkedin">15.08.2012</date>
                <date payment="12.95" state="pending">16.08.2012</date>
            </dates>
        </allocation>
     */
    function toXml() {
        /* create a dom document with encoding utf8 */
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }
    
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/allocation_row.xsl');
        
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