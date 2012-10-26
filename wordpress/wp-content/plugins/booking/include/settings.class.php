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
        $xmlRoot->appendChild($domtree->createElement('bookingsurl', get_option('hbo_bookings_url')));
        $xmlRoot->appendChild($domtree->createElement('allocationsurl', get_option('hbo_allocations_url')));
        $xmlRoot->appendChild($domtree->createElement('summaryurl', get_option('hbo_summary_url')));
        $xmlRoot->appendChild($domtree->createElement('editbookingurl', get_option('hbo_editbooking_url')));
        $xmlRoot->appendChild($domtree->createElement('resourcesurl', get_option('hbo_resources_url')));
        $xmlRoot->appendChild($domtree->createElement('delete_on_deactivate', get_option('hbo_delete_db_on_deactivate')));
    }

    /**
     * Updates the option values for all relevant settings.
     * $optionsArray : array of option name => option values
     */
    function updateOptions($optionsArray) {
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_bookings_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_allocations_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_summary_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_editbooking_url');
        $this->setOptionIfNotEmpty($optionsArray, 'hbo_resources_url');

        update_option('hbo_delete_db_on_deactivate', isset($optionsArray['hbo_delete_db_on_deactivate']) ? 'On' : 'Off');
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
