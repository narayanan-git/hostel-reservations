<?php

/**
 * Hostel Backoffice Settings controller.
 */
class Settings extends XslTransform {

    /** 
     * Default constructor.
     */
    function Settings() {
    }

    /**
     * Adds this allocation table to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {    
        // create the root element for this class and append it to our parent
        $xmlRoot = $parentElement->appendChild($domtree->createElement('view'));
        $xmlRoot->appendChild($domtree->createElement('bookingsurl', get_option('bookings_url')));
        $xmlRoot->appendChild($domtree->createElement('allocationsurl', get_option('allocations_url')));
        $xmlRoot->appendChild($domtree->createElement('summaryurl', get_option('summary_url')));
        $xmlRoot->appendChild($domtree->createElement('editbookingurl', get_option('editbooking_url')));
        $xmlRoot->appendChild($domtree->createElement('resourcesurl', get_option('resources_url')));
    }

    /**
     * Updates the option values for all relevant settings.
     * $optionsArray : array of option name => option values
     */
    function updateOptions($optionsArray) {
        $this->setOptionIfNotEmpty($optionsArray, 'bookings_url');
        $this->setOptionIfNotEmpty($optionsArray, 'allocations_url');
        $this->setOptionIfNotEmpty($optionsArray, 'summary_url');
        $this->setOptionIfNotEmpty($optionsArray, 'editbooking_url');
        $this->setOptionIfNotEmpty($optionsArray, 'resources_url');
    }

    /**
     * Updates the optionName with the associated value in optionsArray
     * if it exists and is not blank.
     * $optionsArray : array of option name => option values
     * $optionName : option name to save
     */
    function setOptionIfNotEmpty($optionsArray, $optionName) {
        if (isset($optionsArray[$optionName]) && $optionsArray[$optionName] != '') {
            update_option($optionName, $optionsArray[$optionName]);
        }
    }

    /** 
     * Writes XML in the following syntax:
     * <view>
     *     <firstdayofweek>0</firstdayofweek>
     *     ...
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/settings.xsl';
    }
    
}

?>
