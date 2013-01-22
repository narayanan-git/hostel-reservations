<?php

/**
 * Create random booking/allocation data.
 */
class GenerateTestData extends XslTransform {

    var $lastCommand;

    /** 
     * Default constructor.
     */
    function GenerateTestData() {
        $this->lastCommand = array();
    }

    /**
     * Fetches this page in the following format:
     * <view>
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        foreach ($this->lastCommand as $lc) {
            $xmlRoot->appendChild($domtree->createElement('lastCommand', $lc));
        }
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/generate_test_data.xsl';
    }
}

?>
