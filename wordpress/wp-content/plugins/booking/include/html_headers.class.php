<?php

/**
 * XslTransform to generate the include scripts that should appear in the "head" section
 * of the current page being displayed.
 */    
class HtmlHeaders extends XslTransform {

    /**
     * Default constructor.
     */
    function HtmlHeaders() {
    }

    /** 
     * Writes XML in the following syntax:
     * <view>
     *     <pluginurl>http://myhost:8080//wp-content/plugins/myplugin</pluginurl>
     *     <pluginfilename>myplugin.php</pluginfilename>
     *     <locale>en_US</locale>
     *     ...
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        // create the root element for this class and append it to the root
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $xmlRoot->appendChild($domtree->createElement('pluginurl', WPDEV_BK_PLUGIN_URL));
        $xmlRoot->appendChild($domtree->createElement('pluginfilename', WPDEV_BK_PLUGIN_FILENAME));
        $xmlRoot->appendChild($domtree->createElement('locale', 'en_US'));  // hard-coded for now
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/html_headers.xsl';
    }
}

?>
