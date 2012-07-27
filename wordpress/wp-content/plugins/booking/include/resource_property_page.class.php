<?php

/**
 * Page to assign/view resource properties.
 */
class ResourcePropertyPage extends XslTransform {

    var $resourceId;  // id of resource we are editing
    var $isSaved;     // boolean : true if saved

    /**
     * Default constructor.
     * $resourceId : id of resource currently being edited
     */
    function ResourcePropertyPage($resourceId = '') {
        $this->resourceId = $resourceId;
        $this->isSaved = false;
    }
   
    /**
     * Adds this resource page to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this class and append it to our parent
        $xmlRoot = $domtree->createElement('view');
        $xmlRoot = $parentElement->appendChild($xmlRoot);

        if ($this->isSaved) {
            $xmlRoot->appendChild($domtree->createElement('saved', 'true'));
        }
        
        $resource = ResourceDBO::fetchResourceById($this->resourceId);
        $xmlRoot->appendChild($domtree->createElement('resourceId', $this->resourceId));
        $xmlRoot->appendChild($domtree->createElement('resourceName', $resource->name));
        $propertiesElem = $xmlRoot->appendChild($domtree->createElement('properties'));
        
        foreach (ResourceDBO::getPropertiesForResource($this->resourceId) as $prop) {
            $propRow = $domtree->createElement('property');

            if ($prop->selected_yn == 'Y') {
                $attrSelected = $domtree->createAttribute('selected');
                $attrSelected->value = 'true';
                $propRow->appendChild($attrSelected);
            }
            
            $propRow->appendChild($domtree->createElement('id', $prop->property_id));
            $propRow->appendChild($domtree->createElement('value', $prop->description));
            $propertiesElem->appendChild($propRow);
        }
    }

    /**
     * Fetches this page in the following format:
     * <view>
     *     <saved>true</saved>
     *     <resourceId>14</resourceId>
     *     <resourceName>Room 13</resourceName>
     *     <properties>
     *         <property selected="true">
     *             <id>1</id>
     *             <value>4-Bed Dorm</value>
     *         </property>
     *         <property>
     *             <id>2</id>
     *             <value>8-Bed Dorm</value>
     *         </property>
     *         <property selected="true">
     *             <id>3</id>
     *             <value>Room with a View</value>
     *         </property>
     *     <properties>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/resource_property_page.xsl';
    }
}

?>