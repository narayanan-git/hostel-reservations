<?php

/**
 * Booking Resources Management controller.
 */
abstract class XslTransform {

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    abstract function getXslFilename();
    
    /**
     * Returns the XML used during XSL transform.
     */
    abstract function toXml();

    /**
     * Transforms this class to HTML using the given stylesheet.
     */
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load($this->getXslFilename());
        
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
            //trigger_error('XSL transformation failed.', E_USER_ERROR);
            error_log( "XSL transformation failed: " . $this->toXml() );
            return 'XSL transformation failed.';
        } // if 
        return 'XSL transformation failed.';
    }

}

?>