<?php

/**
 * Booking Resources Management controller.
 */
class Resources extends ResourcesTable {

    // error message from last action if there was any
    var $errorMessage;

    /**
     * Default constructor.
     * $editResourceId : id of resource currently being edited
     */
    function Resources($editResourceId = '') {
        parent::ResourcesTable($editResourceId);
    }
    
    /**
     * Adds this allocation table to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {    
        // create the root element for this class and append it to our parent
        $xmlRoot = $domtree->createElement('view');
        $xmlRoot->appendChild($domtree->createElement('homeurl', home_url()));
        $xmlRoot = $parentElement->appendChild($xmlRoot);
        if (isset($this->errorMessage)) {
            $xmlRoot->appendChild($domtree->createElement('errorMessage', $this->errorMessage));
        }
        parent::addSelfToDocument($domtree, $xmlRoot);
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/resources.xsl';
    }

}

?>