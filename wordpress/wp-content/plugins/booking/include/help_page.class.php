<?php

/**
 * Controller class for generating pre-defined help pages.
 */
class HelpPage extends XslTransform {

    var $name;  // name of page view we are currently generating

    /** 
     * Default constructor.
     * $name : name of view (page) to be generated
     */
    function HelpPage($name = 'help') {
        $this->name = $name;
    }

    /**
     * Sets the name of this page before generating the HTML and returning it.
     * $name : name of view (page) to be generated
     * Returns generated HTML
     */
    function toHtml($name) {
        $this->name = $name;
        return parent::toHtml();
    }

    /**
     * Fetches this page in the following format:
     * <view>
     *     <name>add-edit-booking</name>
     *     <homeurl>http://yourdomain.com/subdomain</homeurl>
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $xmlRoot->appendChild($domtree->createElement('name', $this->name));
        $xmlRoot->appendChild($domtree->createElement('homeurl', home_url()));
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/help_page.xsl';
    }
}

?>
