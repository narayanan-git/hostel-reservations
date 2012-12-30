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
     * DELETES and recreates all test data in the database.
     */
    function reloadTestData() {
        $hpFactory = new HelpPageFactory();
        $hp_id = $hpFactory->createHelpPages();
        $this->lastCommand[] = 'Done generating help pages... '.$hp_id;
    }

    function getScriptOutput() {
        return implode(',', $this->lastCommand);
    }

    /**
     * Fetches this page in the following format:
     * <view>
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $xmlRoot->appendChild($domtree->createElement('lastCommand', $this->getScriptOutput()));
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
