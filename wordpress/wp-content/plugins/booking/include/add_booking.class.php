<?php

/**
 * Controller for Add Booking form.
 */
class AddBooking {

    // contains a comma-delimited list of currently selected dates?? may be useful later for edit booking
    var $my_selected_dates_without_calendar;

    function AddBooking($my_selected_dates_without_calendar = '') {
        $this->my_selected_dates_without_calendar = $my_selected_dates_without_calendar;
    }

    /** 
      Generates the following xml:
        <addbooking>
            <selectedDates>15.08.2012, 16.08.2012</selectedDates>
            <resources>
                <resource>
                    <id>1</id>
                    <name>8-Bed Dorm</name>
                    <level>1</level>
                </resource>
                <resource>
                    <id>2</id>
                    <name>Bed A</name>
                    <level>2</level>
                </resource>
                ...
            </resources>
        </addbooking>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
    
        // create the root element of the xml tree
        $xmlRoot = $domtree->createElement('addbooking');
        $xmlRoot = $domtree->appendChild($xmlRoot);
    
        $xmlRoot->appendChild($domtree->createElement('selectedDates', $this->my_selected_dates_without_calendar));
        $resourcesRoot = $domtree->createElement('resources');
        $xmlRoot = $xmlRoot->appendChild($resourcesRoot);

        foreach (ResourceDBO::getAllResources() as $res) {
            $resourceRow = $domtree->createElement('resource');
            $resourceRow->appendChild($domtree->createElement('id', $res->resource_id));
            $resourceRow->appendChild($domtree->createElement('name', $res->name));
            $resourceRow->appendChild($domtree->createElement('level', $res->lvl));
            $resourcesRoot->appendChild($resourceRow);
        }
        return $domtree->saveXML();
    }
    
    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/add_booking.xsl');
        
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