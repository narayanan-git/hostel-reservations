<?php

/**
 * Booking Resource data object.
 */
class BookingResource extends XslTransform {

    var $resourceId;
    var $name;
    var $level;
    var $path;
    var $numberChildren;
    var $type;       // resource type, e.g. group, room, bed
    var $freebeds;   // array() of free beds (if type is 'group' or 'room') in same order as allocationCells
    var $unpaid = false;  // mark this resource as unpaid (this property is date specific)
    private $childResources;  // array of BookingResource (where this is a parent resource, ie numberChildren > 0)
    private $allocationCells;  // (optional) array of AllocationCell assigned to this resource (where this is a child node, ie. numberChildren = 0)
    
    /**
     * Default constructor.
     */
    function BookingResource( $resourceId, $name, $level, $path, $numberChildren, $type ) {
        $this->resourceId = $resourceId;
        $this->name = $name;
        $this->level = $level;
        $this->path = $path;
        $this->numberChildren = $numberChildren;
        $this->type = $type;
        $this->freebeds = array();
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
     * Updates the number of free beds for this resource if it is a 'group' or a 'room'
     * based on the current set of allocationCells.
     * freeBeds will be initialised with the number of free beds in the group/room using a positional
     * index from 0 .. length($allocationCells) - 1
     */
    function updateFreeBeds() {
        // iterate through each allocation in order and tally up the number of free beds
        $this->freebeds = array();  // int array() indexed by position in allocationCells (count from 0)

        if ($this->type == 'group' || $this->type == 'room') {
            foreach ($this->childResources as $child) {
                $child->updateFreeBeds();   // count children
                
                if ($this->type == 'room') {
                    $i = 0;
                    foreach ($child->allocationCells as $cell) {
                        if (false === isset($this->freebeds[$i])) {
                            $this->freebeds[$i] = 0;
                        }
                        if ($cell->id == 0) {
                            $this->freebeds[$i]++;
                        }
                        $i++;
                    }

                } else {
                    // tally up all child counts by appending
                    foreach ($child->freebeds as $key => $value) {
                        if (isset($this->freebeds[$key])) {
                            $this->freebeds[$key] += $value;
                        } else {
                            $this->freebeds[$key] = $value;
                        }
                    }
                }
            }
        }

        if ($this->type == 'private') {
            // TODO: count the number of empty rooms
        }
    }

    /**
     * Goes through each of our child resources and updates the 'unpaid' flag to true
     * for the given resource ids.
     * $resourceIds : array() of resource ids to mark as unpaid
     */
    function markUnpaidResources($resourceIds) {
        if (sizeof($resourceIds) > 0) {
            if (in_array($this->resourceId, $resourceIds)) {
                $this->unpaid = true;
            }
        
            foreach ($this->childResources as $child) {
                $child->markUnpaidResources($resourceIds);
            }
        }
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
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
        $parentElement->appendChild($domtree->createElement('path', $this->path));
        $parentElement->appendChild($domtree->createElement('level', $this->level));
        $parentElement->appendChild($domtree->createElement('numberChildren', $this->numberChildren));
        
        if ($this->unpaid) {
            $parentElement->appendChild($domtree->createElement('unpaid', 'true'));
        }

        if ($this->numberChildren > 0) {
            $this->updateFreeBeds();   // this will be req'd for the daily summary
            $freeBedRoot = $parentElement->appendChild($domtree->createElement('freebeds'));
            for ($i = 0; $i < count($this->freebeds); $i++) {
                $freeBedRoot->appendChild($domtree->createElement('freebed', $this->freebeds[$i]));
            }
        }
        
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
     *         <path>/1</path>
     *         <level>1</level>
     *         <numberChildren>2</numberChildren>
     *         <freebeds>
     *             <freebed>3</freebed>
     *             ...
     *         </freebeds>
     *         <resource>...</resource>
     *         <resource>...</resource>
     *     </resource>
     *
     * For a child resource:
     *     <resource>
     *         <id>2</id>
     *         <name>8-Bed Dorm</name>
     *         <path>/1/2</path>
     *         <level>2</level>
     *         <numberChildren>0</numberChildren>
     *         <unpaid>true</unpaid>
     *         <cells>
     *             <allocationcell> ... </allocationcell>
     *             <allocationcell> ... </allocationcell>
     *         <cells>
     *     </resource>
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
        return WPDEV_BK_PLUGIN_DIR. '/include/resources.xsl';
    }

}

?>