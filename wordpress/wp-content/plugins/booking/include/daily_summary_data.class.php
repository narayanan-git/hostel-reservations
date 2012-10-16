<?php

/**
 * Display controller for daily summary content on the daily summary page.
 */
class DailySummaryData extends XslTransform {

    var $selectionDate;  // DateTime of selected date
    
    private $dailySummaryResources;  // array() of DailySummaryResource
    private $allocationView;   // AllocationView showing free beds
    
    /**
     * Default constructor.
     * $selectionDate : date to display summary for (DateTime)
     */
    function DailySummaryData($selectionDate) {
        $this->selectionDate = $selectionDate;

        // show view from yesterday to 7 days in future
        $startDate = clone $selectionDate;
        $startDate->sub(new DateInterval('P1D'));
        $endDate = clone $startDate;
        $endDate->add(new DateInterval('P8D'));
        $this->allocationView = new AllocationView($startDate, $endDate);
    }
    
    /**
     * Updates the data required for the given date.
     */
    function doSummaryUpdate() {
        $this->dailySummaryResources = ResourceDBO::fetchDailySummaryResources($this->selectionDate);
        $this->allocationView->doSearch();
        $this->allocationView->markUnpaidResources($this->selectionDate);
    }
    
    /**
     * Tallies up the checkedInCount values from all dailySummaryResources.
     * Returns non-null integer.
     */
    function getCheckedInCount() {
        $return_val = 0;
        foreach ($this->dailySummaryResources as $res) {
            $return_val += $res->checkedInCount;
        }
        return $return_val;
    }

    /**
     * Tallies up the checkedInRemaining values from all dailySummaryResources.
     * Returns non-null integer.
     */
    function getCheckedInRemaining() {
        $return_val = 0;
        foreach ($this->dailySummaryResources as $res) {
            $return_val += $res->checkedInRemaining;
        }
        return $return_val;
    }

    /**
     * Tallies up the checkedOutCount values from all dailySummaryResources.
     * Returns non-null integer.
     */
    function getCheckedOutCount() {
        $return_val = 0;
        foreach ($this->dailySummaryResources as $res) {
            $return_val += $res->checkedOutCount;
        }
        return $return_val;
    }

    /**
     * Tallies up the checkedOutRemaining values from all dailySummaryResources.
     * Returns non-null integer.
     */
    function getCheckedOutRemaining() {
        $return_val = 0;
        foreach ($this->dailySummaryResources as $res) {
            $return_val += $res->checkedOutRemaining;
        }
        return $return_val;
    }

    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $xmlRoot = $parentElement->appendChild($domtree->createElement('dataview'));
        $xmlRoot->appendChild($domtree->createElement('selectiondate', $this->selectionDate->format('d.m.Y')));
        
        $checkinRoot = $xmlRoot->appendChild($domtree->createElement('checkins'));
        
        $attrArrived = $domtree->createAttribute('arrived');
        $attrArrived->value = $this->getCheckedInCount();
        $checkinRoot->appendChild($attrArrived);
        
        $attrRemaining = $domtree->createAttribute('remaining');
        $attrRemaining->value = $this->getCheckedInRemaining();
        $checkinRoot->appendChild($attrRemaining);
        
        foreach ($this->dailySummaryResources as $res) {
            $res->addCheckInsToDocument($domtree, $checkinRoot);
        }
        
        $checkoutRoot = $xmlRoot->appendChild($domtree->createElement('checkouts'));

        $attrDeparted = $domtree->createAttribute('departed');
        $attrDeparted->value = $this->getCheckedOutCount();
        $checkoutRoot->appendChild($attrDeparted);

        $attrRemaining = $domtree->createAttribute('remaining');
        $attrRemaining->value = $this->getCheckedOutRemaining();
        $checkoutRoot->appendChild($attrRemaining);

        foreach ($this->dailySummaryResources as $res) {
            $res->addCheckOutsToDocument($domtree, $checkoutRoot);
        }
    }
    
    /** 
      Generates the following xml:
        <view>
            <selectiondate>21.05.2012</selectiondate>
            <checkins arrived="41" remaining="101">
                <checkin arrived="21" remaining="84">
                    <caption>10-Bed Dorms</caption>
                    <checkin arrived="7" remaining="1">
                        <caption>Room 10</caption>
                    </checkin>
                    ...
                </checkin>
                <checkin arrived="51" remaining="13">
                    <caption>12-Bed Dorms</caption>
                    <checkin arrived="10" remaining="3">
                        <caption>Room 21</caption>
                    </checkin>
                    ...
                </checkin>
                ...
            </checkins>
            <checkouts departed="21" remaining="31">
                <checkout departed="12" remaining="18">
                    <caption>10 Bed Dorms</caption>
                    <checkout departed="3" remaining="2">
                        <caption>Room 10</caption>
                    </checkout>
                </checkout>
                <checkout departed="7" remaining="2">
                    <caption>12 Bed Dorms</caption>
                    <checkout departed="5" remaining="1">
                        <caption>Room 11</caption>
                    </checkout>
                </checkout>
            </checkouts>
            <allocationview>
                ...
            </allocationview>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        $this->allocationView->addSelfToDocument($domtree, $xmlRoot);
        $return_val = $domtree->saveXML();
error_log($return_val);
        return $return_val;
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/daily_summary_data.xsl';
    }

}

?>