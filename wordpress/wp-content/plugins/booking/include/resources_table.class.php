<?php

/**
 * Booking Resources table controller and view.
 */
class ResourcesTable extends XslTransform {

    // the current resource id that is being edited
    var $editResourceId;

    /**
     * Default constructor.
     * $editResourceId : id of resource currently being edited
     */
    function ResourcesTable($editResourceId = '') {
        $this->editResourceId = $editResourceId;
    }

    /**
     * Adds this allocation table to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this class and append it to our parent
        $xmlRoot = $domtree->createElement('resources');
        $xmlRoot = $parentElement->appendChild($xmlRoot);
    
        // if we are editing, then we create a new element
        if( $this->editResourceId != '' ) {
            $xmlRoot->appendChild($domtree->createElement('editResource', $this->editResourceId));
        }
        
        foreach (ResourceDBO::getAllResources() as $res) {
            $resourceRow = $domtree->createElement('resource');
            $resourceRow->appendChild($domtree->createElement('id', $res->resource_id));
            $resourceRow->appendChild($domtree->createElement('name', $res->name));
            $resourceRow->appendChild($domtree->createElement('path', $res->path));
            $resourceRow->appendChild($domtree->createElement('level', $res->lvl));
            $resourceRow->appendChild($domtree->createElement('numberChildren', $res->number_children));
            $resourceRow->appendChild($domtree->createElement('type', $res->resource_type));
            $resourceRow->appendChild($domtree->createElement('roomType', $res->room_type));
            $xmlRoot->appendChild($resourceRow);
        }
    }

    /**
     * Fetches all resources in the following format:
     * <resources>
     *     <editResource>14</editResource>
     *     <resource>
     *         <id>1</id>
     *         <name>8-Bed Dorm</name>
     *         <capacity></capacity>
     *         <path>/1</path>
     *         <level>1</level>
     *         <numberChildren>2</numberChildren>
     *         <type>room</type>
     *         <roomType>M</roomType>     
     *     </resource>
     *     <resource>
     *         <id>2</id>
     *         <name>Bed A</name>
     *         <capacity>1</capacity>
     *         <path>/1/2</path>
     *         <level>2</level>
     *         <numberChildren>0</numberChildren>
     *         <type>bed</type>
     *     </resource>
     *     <resource>
     *         <id>3</id>
     *         <name>Bed B</name>
     *         <capacity>1</capacity>
     *         <path>/1/3</path>
     *         <level>2</level>
     *         <numberChildren>0</numberChildren>
     *         <type>bed</type>
     *     </resource>
     *     ...
     * </resources>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/resources_table.xsl';
    }

}

?>