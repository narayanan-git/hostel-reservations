<?php

/**
 * A single booking date for an allocation.
 */
class BookingDate {
    var $allocationId;
    var $bookingDate;  // DateTime
    var $status;  // paid, free, reserved, etc..
    var $checkedOut;  // null or boolean
    
    /**
     * Default constructor.
     * $allocationId : allocation id to which this booking date belongs
     * $bookingDate : DateTime (single date of a booking)
     * $status : status (e.g. paid, free...)
     * $checkedOut : boolean true if guest has checked out on this date (default false)
     */
    function BookingDate($allocationId, $bookingDate, $status, $checkedOut = false) {
        $this->allocationId = $allocationId;
        $this->bookingDate = $bookingDate;
        $this->status = $status;
        $this->checkedOut = $checkedOut;
    }
}

?>