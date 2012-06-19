<?php

/**
 * Booking Resource data object.
 */
class BookingResource {

    var $resourceId;
    var $name;
    var $capacity;
    var $level;
    var $path;
    var $numberChildren;
    var $type;
    private $childResources;  // array of BookingResource (where this is a parent resource, ie numberChildren > 0)
    private $allocationCells;  // (optional) array of AllocationCell assigned to this resource (where this is a child node, ie. numberChildren = 0)
    
    /**
     * Default constructor.
     */
    function BookingResource( $resourceId, $name, $capacity, $level, $path, $numberChildren, $type ) {
        $this->resourceId = $resourceId;
        $this->name = $name;
        $this->capacity = $capacity;
        $this->level = $level;
        $this->path = $path;
        $this->numberChildren = $numberChildren;
        $this->type = $type;
        $this->childResources = array();
        $this->allocationCells = array();
    }
    
    /**
     * Adds child resource to this object's list of children.
     * $childResource : BookingResource to add as child
     */
    function addChildResource($childResource) {
        $this->childResources[] = $childResource;
    }
    
    /**
     * Sets the allocation cells assigned for this resource.
     * This is transaction specific; for any particular set of dates
     * the allocation cells may vary.
     * $allocationCells : array of AllocationCell
     */
    function setAllocationCells($allocationCells) {
        $this->allocationCells = $allocationCells;
    }

    /**
     * Adds this allocation row to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this row will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        // create the root element for this allocation row
        $resourceRoot = $domtree->createElement('resource');
        $parentElement = $parentElement->appendChild($resourceRoot);

        $parentElement->appendChild($domtree->createElement('id', $this->resourceId));
        $parentElement->appendChild($domtree->createElement('name', $this->name));
        $parentElement->appendChild($domtree->createElement('capacity', $this->capacity));
        $parentElement->appendChild($domtree->createElement('path', $this->path));
        $parentElement->appendChild($domtree->createElement('level', $this->level));
        $parentElement->appendChild($domtree->createElement('numberChildren', $this->numberChildren));
        $parentElement->appendChild($domtree->createElement('type', $this->type));
    
        foreach ($this->childResources as $res) {
            $res->addSelfToDocument($domtree, $parentElement);
        }

        $cells = $parentElement->appendChild($domtree->createElement('cells'));
        foreach ($this->allocationCells as $alloc) {
            $alloc->addSelfToDocument($domtree, $cells);
        }
    }
    
    /**
     * Fetches resource in the following format:
     * For a parent resource:
     *     <resource>
     *         <id>1</id>
     *         <name>8-Bed Dorm</name>
     *         <capacity></capacity>
     *         <path>/1</path>
     *         <level>1</level>
     *         <numberChildren>2</numberChildren>
     *         <resource>...</resource>
     *         <resource>...</resource>
     *     </resource>
     *
     * For a child resource:
     *     <resource>
     *         <id>2</id>
     *         <name>8-Bed Dorm</name>
     *         <capacity></capacity>
     *         <path>/1/2</path>
     *         <level>2</level>
     *         <numberChildren>0</numberChildren>
     *         <cells>
     *             <allocationcell span="2"> ... </allocationcell>
     *             <allocationcell span="1"> ... </allocationcell>
     *         <cells>
     *     </resource>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $this->addSelfToDocument($domtree, $domtree);
        return $domtree->saveXML();
    }

    function toHtml() {
        // create a DOM document and load the XSL stylesheet
        $xsl = new DomDocument;
        $xsl->load(WPDEV_BK_PLUGIN_DIR. '/include/resources.xsl');
        
        // import the XSL styelsheet into the XSLT process
        $xp = new XsltProcessor();
        $xp->importStylesheet($xsl);
        
        // create a DOM document and load the XML datat
        $xml_doc = new DomDocument;
        $xml_doc->loadXML(Resources::toXml());
        
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