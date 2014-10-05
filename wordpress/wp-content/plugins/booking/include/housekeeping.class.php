<?php

/**
 * Display controller for housekeeping page.
 */
class HouseKeeping extends XslTransform {

    var $selectionDate;  // DateTime of selected date

    var $bedsheetView;
    var $jobInfo;
    
    /**
     * Default constructor.
     * $selectionDate : date to display housekeeping for (DateTime)
     */
    function HouseKeeping() {
    }
    
    /**
     * Updates the view data for the given date.
     */
    function doViewForDate($selectionDate) {
        $this->selectionDate = $selectionDate;

        // show view from 3 days ago
        $startDate = clone $selectionDate;
//        $startDate->sub(new DateInterval('P3D'));   FIXME

        $this->jobInfo = LilHotelierDBO::getLatestJobOfType( "bedsheets" );
error_log( 'job resultset: ' . var_export( $this->jobInfo, TRUE ) );

        if( $this->jobInfo ) {
            //$this->bedsheetView = LilHotelierDBO::fetchBedSheetsFrom($startDate, $this->jobInfo->job_id);
            $this->bedsheetView = LilHotelierDBO::fetchBedSheetsFrom(
            //DateTime::createFromFormat('!Y-m-d', '2014-08-10', new DateTimeZone('UTC')), 
            $selectionDate,
            $this->jobInfo->job_id);
        }
    }
    
    /**
     * Adds this object to the DOMDocument/XMLElement specified.
     * See toXml() for details.
     * $domtree : DOM document root
     * $parentElement : DOM element where this object will be added
     */
    function addSelfToDocument($domtree, $parentElement) {
        $parentElement->appendChild($domtree->createElement('homeurl', home_url()));
        $parentElement->appendChild($domtree->createElement('selectiondate', $this->selectionDate->format('Y-m-d')));

        // ignore these rooms
        if( ! $this->isNullOrEmptyString( get_option('hbo_housekeeping_ignore_rooms') ) ) {
            $ignoredRooms = explode(",", get_option('hbo_housekeeping_ignore_rooms'));
            $ignoreRoot = $parentElement->appendChild($domtree->createElement('ignore_rooms'));
            foreach( $ignoredRooms as $room ) {
                $ignoreRoot->appendChild( $domtree->createElement('room', trim($room) ));
            }
        }

        if( $this->jobInfo ) {
            // job_id, name, status, created_date, last_updated_date
            $jobRoot = $parentElement->appendChild($domtree->createElement('job'));
//error_log( 'bedhseetView' . var_export( $this->bedsheetView, TRUE ) );
//error_log( 'job' . var_export( $this->row, TRUE ) );
            $jobRoot->appendChild($domtree->createElement('id', $this->jobInfo->job_id));
            $jobRoot->appendChild($domtree->createElement('name', $this->jobInfo->name));
            $jobRoot->appendChild($domtree->createElement('status', $this->jobInfo->status));
            $jobRoot->appendChild($domtree->createElement('created_date', $this->jobInfo->created_date));
            $jobRoot->appendChild($domtree->createElement('last_updated_date', $this->jobInfo->last_updated_date));
        }

        if ( $this->bedsheetView ) {
            foreach( $this->bedsheetView as $bed ) {
error_log( "BED : " . var_export( $bed, TRUE ));
                $bedRoot = $parentElement->appendChild($domtree->createElement('bed'));
                $bedRoot->appendChild($domtree->createElement('room', $bed->room));
                $bedRoot->appendChild($domtree->createElement('bed_name', htmlspecialchars($bed->bed_name)));
                $bedRoot->appendChild($domtree->createElement('guest_name', $bed->guest_name));
                $bedRoot->appendChild($domtree->createElement('checkin_date', $bed->checkin_date));
                $bedRoot->appendChild($domtree->createElement('checkout_date', $bed->checkout_date));
                $bedRoot->appendChild($domtree->createElement('data_href', $bed->data_href));
                $bedRoot->appendChild($domtree->createElement('created_date', $bed->created_date));
                $bedRoot->appendChild($domtree->createElement('bedsheet', $bed->bedsheet));
            }
        }
    }
    
    // Function for basic field validation (present and neither empty nor only white space
    function isNullOrEmptyString($value){
        return ! isset($value) || trim($value)==='';
    }

    /** 
      Generates the following xml:
        <view>
            <homeurl>/yourwebapp/</homeurl>
            <selectiondate>2012-05-21</selectiondate>
            <job>
              <id>5</id>
              <name>calendar</name>
              <status>completed</status>
              <created_date>15.03.2014</created_date>
              <last_updated_date>22.03.2014</last_updated_date>
            </job>
        </view>
     */
    function toXml() {
        // create a dom document with encoding utf8
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $this->addSelfToDocument($domtree, $xmlRoot);
        $xml = $domtree->saveXML();
error_log( $xml );
        return $xml;
    }
    
    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/housekeeping.xsl';
    }

}

?>