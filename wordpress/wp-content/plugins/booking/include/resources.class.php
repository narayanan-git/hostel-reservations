<?php

/**
 * Booking Resources Management controller.
 */
class Resources {

    /**
     * Fetches all resources in the following format:
     * <resources>
     *     <resource>
     *         <id>1</id>
     *         <name>8-Bed Dorm</name>
     *         <capacity></capacity>
     *         <parentResourceId></parentResourceId>
     *         <numberChildren>2</numberChildren>
     *     </resource>
     *     <resource>
     *         <id>2</id>
     *         <name>Bed A</name>
     *         <capacity>1</capacity>
     *         <parentResourceId>1</parentResourceId>
     *     </resource>
     *     <resource>
     *         <id>3</id>
     *         <name>Bed B</name>
     *         <capacity>1</capacity>
     *         <parentResourceId>1</parentResourceId>
     *     </resource>
     *     ...
     * </resources>
     */
    static function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        // create the root element for this allocation row
        $xmlRoot = $domtree->createElement('resources');
        $xmlRoot = $domtree->appendChild($xmlRoot);
    
        foreach (ResourceDBO::getAllResources() as $res) {
            $resourceRow = $domtree->createElement('resource');
            $resourceRow->appendChild($domtree->createElement('id', $res->resource_id));
            $resourceRow->appendChild($domtree->createElement('name', $res->name));
            $resourceRow->appendChild($domtree->createElement('capacity', $res->capacity));
            $resourceRow->appendChild($domtree->createElement('parentResourceId', $res->parent_resource_id));
            $resourceRow->appendChild($domtree->createElement('numberChildren', $res->number_children));
            $xmlRoot->appendChild($resourceRow);
        }
        return $domtree->saveXML();
    }

    static function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/resources.xsl');
        
        // import the XSL styelsheet into the XSLT process
        $xp = new XsltProcessor();
        $xp->importStylesheet($xsl);
        
        // create a DOM document and load the XML datat
        $xml_doc = new DomDocument;
        $xml_doc->loadXML(Resources::toXml());
        
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