<?php

/**
 * Class for running all unit tests.
 */
class RunUnitTestsContent extends RunUnitTests {

    /** 
     * Default constructor.
     */
    function RunUnitTestsContent() {
        parent::RunUnitTests();
    }

    /**
     * Runs all unit tests. Unit tests are those methods
     * in this class that start with test* 
     * $this->results contains all pass/failure messages.
     */
    function runUnitTests() {

        $msg = '';
        if (isset($_SESSION['WP_HOSTELBACKOFFICE'])) {
            $hbo = $_SESSION['WP_HOSTELBACKOFFICE'];
            try {
                //$msg = $hbo->reset_sample_data();
                $hbo->delete_transactional_data();

            } catch (Exception $e) {
                error_log($e->getMessage());
                $msg = $e->getMessage();
            }
        }

error_log("done generate_test_data: $msg"); 
        echo $msg;

        foreach (get_class_methods($this) as $methodName) {
            if (strpos($methodName, 'test') === 0) {
                $this->runTest($methodName);
            }
        }
    }

    //
    // All unit tests are defined below
    //

    public function testArrayContainsAnElement() {
        // Create the Array fixture.
        $fixture = array();
 
        // Add an element to the Array fixture.
        $fixture[] = 'Element';
 
        // Assert that the size of the Array fixture is 1.
        $this->assertEquals(1, sizeof($fixture), "Expecting array of size 1");
    }

    // create a new booking with a single allocation and verify it saves
    public function testAddBookingMale12Bed() {

        self::createTestBooking(
            "four males", 
            "addBookingMale12Bed", 
            array( "M" => 4, "F" => 0, "X" => 0), // $numVisitors
            118, // $resourceId = Puzzle Room
            "12", // $reqRoomSize
            "M", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014'), // $dates
            array("4")); // $resourceProps: 4 = '12 Bed Dorm'
    }

    // create a new booking with a double allocation and verify it saves
    public function testAddBookingFemaleDormBed() {

        self::createTestBooking(
            "two females", 
            "testAddBookingFemaleDormBed", 
            array( "M" => 0, "F" => 2, "X" => 0), // $numVisitors
            132, // $resourceId = Snow White...
            "10", // $reqRoomSize
            "X", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014'), // $dates
            array()); // $resourceProps
    }

    // create a new booking with a double allocation and verify it saves
    public function testSeparateBookingsInSameRoomFemale() {

        self::createTestBooking(
            "first booking", 
            "testSeparateBookingsInSameRoomFemA", 
            array( "M" => 0, "F" => 4, "X" => 0), // $numVisitors, 
            169, // $resourceId = Whisky
            "10+", // $reqRoomSize
            "F", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014'), // $dates
            array()); // $resourceProps

        self::createTestBooking(
            "second booking", 
            "testSeparateBookingsInSameRoomFemB", 
            array( "M" => 0, "F" => 3, "X" => 0), // $numVisitors, 
            169, // $resourceId = Whisky
            "10+", // $reqRoomSize
            "F", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014'), // $dates
            array()); // $resourceProps
    }

    // create a new booking which exceeds the available capacity of the room
    public function testExceedAvailabilityForDormRoom() {

        // Whisky room has only 5 spaces left; try to add 6 guests
        $booking = new AddBooking();
        $booking->firstname = "third booking";
        $booking->lastname = "testSeparateBookingsInSameRoomFemC";
        $booking->referrer = "Telephone";
        $booking->depositPaid = 9.90;
        $booking->amountToPay = 19.80;

        $booking->addAllocation( 
            array( "M" => 0, "F" => 6, "X" => 0), // $numVisitors
            169, // $resourceId = Whisky
            "10+", // $reqRoomSize
            "F", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014'), // $dates
            array()); // $resourceProps

        // doValidate() does not handle allocation errors
        $errors = $booking->doValidate();
        $this->assertEquals(0, sizeof($errors), "Validation error found on AddBooking");

        try {
            $booking->save();
            $this->assertFail( "Expecting save to fail..." );

        } catch( AllocationException $ex ) {
            $this->assertEquals( "Reservation conflicts with existing reservation", 
                $ex->getMessage(), "Allocation exception expected" );
        }

        $this->assert($booking->id == 0, "Expecting booking id to be zero");

        // verify saved contents
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!Y-m-d', '2014-04-05'), 
            DateTime::createFromFormat('!Y-m-d', '2014-04-05'), 
            'checkin', 
            null, // $resourceId
            null, // $status, 
            "testSeparateBookingsInSameRoomFemC" // $matchName
            );
        $this->assertEquals(0, sizeof($bookingSummaryArr), "Expecting no booking created");
    }

    // create new bookings with back-to-back dates in Whisky room (as above).
    // allocations should use the same resources so we don't have any "gaps"
    public function testAddBookingsForNonOverlappingDatesShouldAllocateOverSameResources() {

        self::createTestBooking(
            "non-overlap A", 
            "NonOverlappingDatesShouldAllocateOverSameResources", 
            array( "M" => 0, "F" => 3, "X" => 0), // $numVisitors
            169, // $resourceId = Whisky
            "10+", // $reqRoomSize
            "F", // $reqRoomType
            array('04.04.2014', '05.04.2014', '06.04.2014'), // $dates
            array()); // $resourceProps

        self::createTestBooking(
            "non-overlap B", 
            "NonOverlappingDatesShouldAllocateOverSameResources", 
            array( "M" => 0, "F" => 2, "X" => 0), // $numVisitors
            169, // $resourceId = Whisky
            "10+", // $reqRoomSize
            "F", // $reqRoomType
            array('07.04.2014', '08.04.2014'), // $dates
            array()); // $resourceProps

        // if the above allocate correctly, then we have space for 2 more for the 5-7 april

        self::createTestBooking(
            "non-overlap C", 
            "NonOverlappingDatesShouldAllocateOverSameResources", 
            array( "M" => 0, "F" => 2, "X" => 0), // $numVisitors
            169, // $resourceId = Whisky
            "10+", // $reqRoomSize
            "F", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014'), // $dates
            array()); // $resourceProps
    }

    // only 1 available bed remains in 169 Whisky...
    // specifying this room should allocate to this bed
    // further allocations should fail
    public function testAllocateOnlyAvailableBedInRoom() {

        // booking for last remaining bed
        self::createTestBooking(
            "alloc remaining bed", 
            "allocateOnlyAvailableBedInRoom", 
            array( "M" => 0, "F" => 1, "X" => 0), // $numVisitors
            169, // $resourceId = Whisky
            "10+", // $reqRoomSize
            "F", // $reqRoomType
            array('07.04.2014'), // $dates
            array()); // $resourceProps

        // attempt to allocate more to room
        $booking = new AddBooking();
        $booking->firstname = "failed booking";
        $booking->lastname = "allocateNoAvailableBedInRoom";
        $booking->referrer = "Telephone";
        $booking->depositPaid = 9.90;
        $booking->amountToPay = 19.80;

        try {
            $booking->addAllocation( 
                array( "M" => 0, "F" => 1, "X" => 0), // $numVisitors
                169, // $resourceId = Whisky
                "10+", // $reqRoomSize
                "F", // $reqRoomType
                array('06.04.2014'), // $dates
                array()); // $resourceProps

            $this->assertFail( "Expecting addAllocation to fail..." );

        } catch( AllocationException $ex ) {
            $this->assertEquals( "Insufficient availability to allocate resource on the specified date(s).", 
                $ex->getMessage(), "Allocation exception expected" );
        }
    }

    // create a booking for a couple into a mixed dorm
    public function testAddCoupleToMixedDorm() {

        self::createTestBooking(
            "couple", 
            "AddCoupleToMixedDorm", 
            array( "M" => 1, "F" => 1, "X" => 0), // $numVisitors
            132, // $resourceId = Snow White...
            "10", // $reqRoomSize
            null, // $reqRoomType
            array('07.04.2014', '08.04.2014', '09.04.2014'), // $dates
            array()); // $resourceProps

        // verify derived room type for the given dates
        $resourceIdDateRoomToRoomType = AllocationDBO::getDerivedRoomTypesForDates( 
            DateTime::createFromFormat('!d.m.Y', "04.04.2014"), 
            DateTime::createFromFormat('!d.m.Y', "10.04.2014"));

        $this->assertEquals( null, $resourceIdDateRoomToRoomType[132]["04.04.2014"], "no room type for 04.04.2014" );
        $this->assertEquals( "FX", $resourceIdDateRoomToRoomType[132]["05.04.2014"], "female/mixed for 05.04.2014" );
        $this->assertEquals( "FX", $resourceIdDateRoomToRoomType[132]["06.04.2014"], "female/mixed for 06.04.2014" );
        $this->assertEquals( "X", $resourceIdDateRoomToRoomType[132]["07.04.2014"], "mixed for 07.04.2014" );
        $this->assertEquals( "X", $resourceIdDateRoomToRoomType[132]["08.04.2014"], "mixed for 08.04.2014" );
        $this->assertEquals( "X", $resourceIdDateRoomToRoomType[132]["09.04.2014"], "mixed for 09.04.2014" );
        $this->assertEquals( null, $resourceIdDateRoomToRoomType[132]["10.04.2014"], "no room type for 10.04.2014" );
    }

    // create a booking specifying a group rather than a room/bed.
    public function testAddToGroupInMixedDorm() {

        self::createTestBooking(
            "mixed group", 
            "AddToGroupInMixedDorm", 
            array( "M" => 2, "F" => 1, "X" => 0), // $numVisitors
            131, // $resourceId = 10 Bed Dorms
            "10", // $reqRoomSize
            "X", // $reqRoomType
            array('07.04.2014'), // $dates
            array()); // $resourceProps

        // they should've been assigned to the first room in the group, 132: Snow White...
        $freeBedIds = AllocationDBO::fetchAvailableBeds( 
            132, // $resourceId = Snow White...
            1, // $numGuests
            "X", // $reqRoomType
            array('07.04.2014'), // $bookingDates
            array(), // $excludedResourceIds
            array() ); // $resourceProps

        $this->assertEquals( 3, sizeof($freeBedIds), "Expecting 3 free beds in Room 12");
    }

    // create a booking with a group larger than what is available
    // in the first room.
    public function testAddToGroupKeepAllocationsInSameRoom() {

        self::createTestBooking(
            "keeptogether", 
            "KeepAllocationsInSameRoom", 
            array( "M" => 2, "F" => 2, "X" => 0), // $numVisitors
            131, // $resourceId = 10 Bed Dorms
            "10", // $reqRoomSize
            "X", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014', '08.04.2014'), // $dates
            array()); // $resourceProps

        // they should've been assigned to the next room in the group, 194: Beetles...
        // 132: Snow White... should be the same as before
        $freeBedIds = AllocationDBO::fetchAvailableBeds( 
            132, // $resourceId = Snow White...
            1, // $numGuests
            "X", // $reqRoomType
            array('07.04.2014'), // $bookingDates
            array(), // $excludedResourceIds
            array() ); // $resourceProps

        $this->assertEquals( 3, sizeof($freeBedIds), "Expecting 3 free beds in Room 12");

        $freeBedIds = AllocationDBO::fetchAvailableBeds( 
            194, // $resourceId = Beetles
            1, // $numGuests
            "X", // $reqRoomType
            array('07.04.2014'), // $bookingDates
            array(), // $excludedResourceIds
            array() ); // $resourceProps

        $this->assertEquals( 6, sizeof($freeBedIds), "Expecting 6 free beds in Room 21");
    }

    // create a booking exceeding the max size of the largest room
    public function testAddGroupBookingExceedingRoomCapacity() {
        self::createTestBooking(
            "largegroup", 
            "SplitAcrossMultipleRooms", 
            array( "M" => 12, "F" => 2, "X" => 0), // $numVisitors
            131, // $resourceId = 10 Bed Dorms
            "10", // $reqRoomSize
            "X", // $reqRoomType
            array('05.04.2014', '06.04.2014', '07.04.2014'), // $dates
            array()); // $resourceProps

        // at the moment, just verify this saved correctly
        // it's not very clever at the moment, this will not
        // attempt to minimize the number of rooms
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', '05.04.2014'), 
            DateTime::createFromFormat('!d.m.Y', '05.04.2014'), 
            'checkin', // $dateMatchType
            null, // $resourceId,
            null, // $status 
            'SplitAcrossMultipleRooms' );

        $this->assertEquals( 1, sizeof($bookingSummaryArr), "expecting 1 matched booking" );
        $bookingSummary = array_shift($bookingSummaryArr);

        // maybe we can improve this one day...
        $this->assertEquals( 3, sizeof($bookingSummary->resources), "booking spread across 3 rooms" );
    } 

    // create a single booking for a private double
    public function testCreatePrivateBookingForDouble() {
        self::createTestBooking(
            "couple", 
            "InPrivate", 
            array( "M" => 1, "F" => 1, "X" => 0), // $numVisitors
            216, // $resourceId = Privates
            "P", // $reqRoomSize
            null, // $reqRoomType
            array('05.04.2014', '06.04.2014'), // $dates
            array( 8 )); // $resourceProps = Double Room

        $freeBedIds = AllocationDBO::fetchAvailableBeds( 
            217, // $resourceId = Double Room
            1, // $numGuests
            null, // $reqRoomType
            array('05.04.2014', '06.04.2014'), // $bookingDates
            array(), // $excludedResourceIds
            array() ); // $resourceProps

        $this->assertEquals( 4, sizeof($freeBedIds), "Expecting 4 free beds in Room 72, 73");
    }

    // create a single booking for one person in a private double
    public function testCreatePrivateDoubleBookingForOnePerson() {
        self::createTestBooking(
            "single", 
            "InPrivate", 
            array( "M" => 1, "F" => 0, "X" => 0), // $numVisitors
            216, // $resourceId = Privates
            "P", // $reqRoomSize
            null, // $reqRoomType
            array('07.04.2014'), // $dates
            array( 8 )); // $resourceProps = Double Room

        $freeBedIds = AllocationDBO::fetchAvailableBeds( 
            216, // $resourceId = Privates
            1, // $numGuests
            null, // $reqRoomType
            array('07.04.2014'), // $bookingDates
            array(), // $excludedResourceIds
            array( 8 ) ); // $resourceProps = Double Room

        // empty bed in assigned room should not be classed as a free bed
        $this->assertEquals( 4, sizeof($freeBedIds), "Expecting 4 free beds in Room 72, 73");
    }

    // create a single booking for 2 double rooms; ensure rooms are not shared w/ others
    public function testCreateMultipleDoubleBookingsShouldAllocateWholeRoomsOnly() {
        self::createTestBooking(
            "multiple", 
            "privateBookings", 
            array( "M" => 1, "F" => 2, "X" => 0), // $numVisitors
            217, // $resourceId = Double Room
            "P", // $reqRoomSize
            null, // $reqRoomType
            array('07.04.2014'), // $dates
            array()); // $resourceProps

        $freeBedIds = AllocationDBO::fetchAvailableBeds( 
            216, // $resourceId = Privates
            1, // $numGuests
            null, // $reqRoomType
            array('07.04.2014'), // $bookingDates
            array(), // $excludedResourceIds
            array( 8 ) ); // $resourceProps = Double Room

        // empty bed in assigned room should not be classed as a free bed
        $this->assertEquals( 0, sizeof($freeBedIds), "Expecting no free beds in double rooms for 07-Apr");
    }

    // create a single booking for 2 double rooms; ensure rooms are not shared w/ others
    public function testCreateDoubleBookingWhenNoAvailabilityShouldFail() {

        $booking = new AddBooking();
        $booking->firstname = "single";
        $booking->lastname = "privateDoubleToFail";
        $booking->referrer = "Telephone";
        $booking->depositPaid = 9.90;
        $booking->amountToPay = 19.80;

        try {
            $booking->addAllocation( 
                array( "M" => 0, "F" => 1, "X" => 0), // $numVisitors
                217, // $resourceId = Double Room
                "P", // $reqRoomSize
                null, // $reqRoomType
                array('07.04.2014'), // $dates
                array()); // $resourceProps

            $this->assertFail( "Expecting addAllocation to fail..." );

        } catch( AllocationException $ex ) {
            $this->assertEquals( "Insufficient availability to allocate resource on the specified date(s).", 
                $ex->getMessage(), "Allocation exception expected" );
        }
    }

    // attempt to extend an allocation into another private booking
    // should most definitely fail
    public function testExtendingDoubleRoomIntoAdditionalBookingShouldFail() {
        
        // extend the current booking in testCreatePrivateBookingForDouble() for one person
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', '05.04.2014'), 
            DateTime::createFromFormat('!d.m.Y', '05.04.2014'), 
            'checkin', 
            null, // $resourceId
            null, // $status, 
            'InPrivate' // $matchName
            );
        $this->assertEquals(1, sizeof($bookingSummaryArr), "Expecting 1 booking created from earlier test");
        
        // verify booking summary query brings back the saved values
        $bookingSummary = array_shift(array_values($bookingSummaryArr));

        // load existing booking
        $booking = new AddBooking();
        $booking->load($bookingSummary->id);
        $allocationArr = $this->queryByXPath('/editbooking/allocations/allocation[name="couple-2"]', $booking->toXml());
        $this->assertEquals(1, sizeof($allocationArr), "expecting 1 allocation with name 'couple-2'");
        $allocation = array_shift($allocationArr);

        // extend booking by 1 day but only for 1 bed into next booking...
        $rowid = (string)$allocation->rowid;
        $booking->toggleBookingStateAt($rowid, "07.04.2014");

        try {
            $booking->save();
            $this->assertFail("Expecting save to fail");

        } catch( AllocationException $ex ) {
            $this->assertEquals( "Reservation conflicts with existing reservation", 
                $ex->getMessage(), "Allocation exception expected" );
        }
    }

    // create a booking into a mixed dorm
    // cancel booking
    // create a new booking into the same room
    public function testCancellationNoShowInDormStillBookable() {

        self::createTestBooking(
            "NoShowFemales", 
            "FemalesInMixedDorm", 
            array( "M" => 0, "F" => 2, "X" => 0), // $numVisitors
            132, // $resourceId = Snow White...
            "10", // $reqRoomSize
            null, // $reqRoomType
            array('09.04.2014', '10.04.2014'), // $dates
            array()); // $resourceProps

        // verify derived room type for the given dates
        $resourceIdDateRoomToRoomType = AllocationDBO::getDerivedRoomTypesForDates( 
            DateTime::createFromFormat('!d.m.Y', "09.04.2014"), 
            DateTime::createFromFormat('!d.m.Y', "10.04.2014"));

        $this->assertEquals( "X", $resourceIdDateRoomToRoomType[132]["09.04.2014"], "mixed for 09.04.2014" );
        $this->assertEquals( "FX", $resourceIdDateRoomToRoomType[132]["10.04.2014"], "female/mixed for 10.04.2014" );

        $freeBedIds = AllocationDBO::fetchAvailableBeds( 
            132, // $resourceId = Snow White...
            1, // $numGuests
            null, // $reqRoomType
            array('09.04.2014', '10.04.2014'), // $bookingDates
            array(), // $excludedResourceIds
            array() ); // $resourceProps

        $this->assertEquals( 6, sizeof($freeBedIds), "Expecting 6 free beds in Snow White room");

        // find the booking that was just created
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', '09.04.2014'), 
            DateTime::createFromFormat('!d.m.Y', '09.04.2014'), 
            'checkin', 
            null, // $resourceId
            null, // $status, 
            'FemalesInMixedDorm' // $matchName
            );
        $this->assertEquals(1, sizeof($bookingSummaryArr), "Expecting 1 booking created");
        
        // verify booking summary query brings back the saved values
        $bookingSummary = array_shift(array_values($bookingSummaryArr));

        // load existing booking
        $booking = new AddBooking();
        $booking->load($bookingSummary->id);
        $allocationArr = $this->queryByXPath('/editbooking/allocations/allocation[name="NoShowFemales-1"]', $booking->toXml());
        $this->assertEquals(1, sizeof($allocationArr), "expecting 1 allocation with name 'NoShowFemales-1'");
        $allocation = array_shift($allocationArr);

        // toggle until cancelled
        $rowid = (string)$allocation->rowid;
        $booking->toggleBookingStateAt($rowid, "09.04.2014");
        $booking->toggleBookingStateAt($rowid, "09.04.2014");
        $booking->toggleBookingStateAt($rowid, "09.04.2014");
        $state = $booking->toggleBookingStateAt($rowid, "09.04.2014");
        $this->assertEquals("cancelled", $state, "expecting state on 09.04.2014 to be 'cancelled'");

        $booking->toggleBookingStateAt($rowid, "10.04.2014");
        $booking->toggleBookingStateAt($rowid, "10.04.2014");
        $booking->toggleBookingStateAt($rowid, "10.04.2014");
        $state = $booking->toggleBookingStateAt($rowid, "10.04.2014");
        $this->assertEquals("cancelled", $state, "expecting state on 10.04.2014 to be 'cancelled'");
        $booking->save();

        // i should be able to fit 9 more people on the 10/11 of April
        self::createTestBooking(
            "FillUpTheRoom", 
            "WithMaxOccupants", 
            array( "M" => 5, "F" => 4, "X" => 0), // $numVisitors
            132, // $resourceId = Snow White...
            "10", // $reqRoomSize
            null, // $reqRoomType
            array('10.04.2014', '11.04.2014'), // $dates
            array()); // $resourceProps

        // verify derived room type for the given dates
        $resourceIdDateRoomToRoomType = AllocationDBO::getDerivedRoomTypesForDates( 
            DateTime::createFromFormat('!d.m.Y', "10.04.2014"), 
            DateTime::createFromFormat('!d.m.Y', "11.04.2014"));

        $this->assertEquals( "X", $resourceIdDateRoomToRoomType[132]["10.04.2014"], "mixed for 10.04.2014" );
        $this->assertEquals( "X", $resourceIdDateRoomToRoomType[132]["11.04.2014"], "mixed for 11.04.2014" );
    }

    // create a new booking with a single allocation
    // remove all dates from the allocation and save
    // save should fail
    public function testRemoveLastBookingDateFromAllocation() {

        self::createTestBooking(
            "single", 
            "allocationWithNoDates", 
            array( "M" => 1, "F" => 0, "X" => 0), // $numVisitors
            118, // $resourceId = Puzzle Room
            "12", // $reqRoomSize
            "M", // $reqRoomType
            array('08.04.2014'), // $dates
            array("4")); // $resourceProps: 4 = '12 Bed Dorm'

        // find the booking that was just created
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            'checkin', 
            null, // $resourceId
            null, // $status, 
            'allocationWithNoDates' // $matchName
            );
        $this->assertEquals(1, sizeof($bookingSummaryArr), "Expecting 1 booking created");
        
        // verify booking summary query brings back the saved values
        $bookingSummary = array_shift(array_values($bookingSummaryArr));

        // load existing booking
        $booking = new AddBooking();
        $booking->load($bookingSummary->id);
        $allocationArr = $this->queryByXPath('/editbooking/allocations/allocation[name="single-1"]', $booking->toXml());
        $this->assertEquals(1, sizeof($allocationArr), "expecting 1 allocation with name 'single-1'");
        $allocation = array_shift($allocationArr);

        // toggle until unselected...
        $rowid = (string)$allocation->rowid;
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $state = $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $this->assertEquals(null, $state, "expecting state on 08.04.2014 to be 'null'");

        try {
            $booking->save();
            $this->assertFail( "Expecting save booking to fail..." );

        } catch( AllocationException $ex ) {
            $this->assertEquals( "At least one date must be specified for an allocation", 
                $ex->getMessage(), "Allocation exception expected" );
        }
    }

    // create a booking for a double room
    // cancel the booking
    // create a new booking for the same room and dates
    // booking should succeed
    public function testCreatePrivateBookingOverCancelledRoomShouldSucceed() {

        self::createTestBooking(
            "private", 
            "privatebooking-cancelled", 
            array( "M" => 0, "F" => 0, "X" => 2), // $numVisitors
            221, // $resourceId = Romeo and Juliet
            "P", // $reqRoomSize
            null, // $reqRoomType
            array('08.04.2014'), // $dates
            array()); // $resourceProps

        // find the booking that was just created
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            'checkin', 
            null, // $resourceId
            null, // $status, 
            'privatebooking-cancelled' // $matchName
            );
        $this->assertEquals(1, sizeof($bookingSummaryArr), "Expecting 1 booking created");
        
        // verify booking summary query brings back the saved values
        $bookingSummary = array_shift(array_values($bookingSummaryArr));

        // load existing booking
        $booking = new AddBooking();
        $booking->load($bookingSummary->id);
        $allocationArr = $this->queryByXPath('/editbooking/allocations/allocation[name="private-1"]', $booking->toXml());
        $this->assertEquals(1, sizeof($allocationArr), "expecting 1 allocation with name 'private-1'");
        $allocation = array_shift($allocationArr);

        // toggle until cancelled
        $rowid = (string)$allocation->rowid;
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $state = $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $this->assertEquals('cancelled', $state, "expecting state on 08.04.2014 to be 'cancelled'");

        $allocationArr = $this->queryByXPath('/editbooking/allocations/allocation[name="private-2"]', $booking->toXml());
        $this->assertEquals(1, sizeof($allocationArr), "expecting 1 allocation with name 'private-2'");
        $allocation = array_shift($allocationArr);

        // toggle until cancelled
        $rowid = (string)$allocation->rowid;
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $state = $booking->toggleBookingStateAt($rowid, "08.04.2014");
        $this->assertEquals('cancelled', $state, "expecting state on 08.04.2014 to be 'cancelled'");

        $booking->save();

        // find the booking that was just cancelled
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            'checkin', 
            null, // $resourceId
            'cancelled', // $status, 
            'privatebooking-cancelled' // $matchName
            );
        $this->assertEquals(1, sizeof($bookingSummaryArr), "Expecting 1 cancelled booking");

        // create new booking overlapping the cancelled booking above
        self::createTestBooking(
            "overlap", 
            "privatebooking-overlaps-cancelled", 
            array( "M" => 1, "F" => 1, "X" => 0), // $numVisitors
            221, // $resourceId = Romeo and Juliet
            "P", // $reqRoomSize
            null, // $reqRoomType
            array('08.04.2014', '09.04.2014'), // $dates
            array()); // $resourceProps

        // find the booking that was just created
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            DateTime::createFromFormat('!d.m.Y', '08.04.2014'), 
            'checkin', 
            null, // $resourceId
            null, // $status, 
            'privatebooking-overlaps-cancelled' // $matchName
            );
        $this->assertEquals(1, sizeof($bookingSummaryArr), "Expecting 1 booking created");
    }

    /**
     * Create a sample booking and verify it is saved correctly.
     *
     * firstName : first name
     * lastName : last name
     * numVisitors : number of guests to add, array indexed by 'M', 'F', 'X'.
     * resourceId : id of resource to assign to (nullable)
     * reqRoomSize : requested room size (e.g. 8, 10, 10+, P, etc..)
     * reqRoomType : requested room type (M/F/X)
     * dates : array of dates (String) in format dd.MM.yyyy
     * resourceProps : array of resource property ids (allocate only to resources with these properties) (optional)
     */
    private function createTestBooking($firstName, $lastName, $numVisitors, $resourceId, $reqRoomSize, $reqRoomType, $dates, $resourceProps = array()) {
        $booking = new AddBooking();
        $booking->firstname = $firstName;
        $booking->lastname = $lastName;
        $booking->referrer = "Telephone";
        $booking->depositPaid = 9.90;
        $booking->amountToPay = 19.80;

        $booking->addAllocation( 
            $numVisitors,
            $resourceId, 
            $reqRoomSize,
            $reqRoomType, 
            $dates,
            array()); // $resourceProps

        $errors = $booking->doValidate();
        $this->assertEquals(0, sizeof($errors), "Validation error found on AddBooking");

        $booking->save();
        $this->assert($booking->id > 0, "Expecting booking id to be non-zero");

        // verify saved contents
        // assumes dates are in chronological order
        $firstDate = array_shift(array_values($dates));
        $bookingSummaryArr = BookingDBO::getBookingsForDateRange(
            DateTime::createFromFormat('!d.m.Y', $firstDate), 
            DateTime::createFromFormat('!d.m.Y', $firstDate), 
            'checkin', 
            null, // $resourceId
            null, // $status, 
            $lastName // $matchName
            );
        $this->assertEquals(1, sizeof($bookingSummaryArr), "Expecting 1 created booking");
        
        // verify booking summary query brings back the saved values
        $bookingSummary = array_shift(array_values($bookingSummaryArr));
        $this->assertEquals($firstName, $bookingSummary->firstname, "firstname");
        $this->assertEquals($lastName, $bookingSummary->lastname, "lastname");
        $this->assertEquals("Telephone", $bookingSummary->referrer, "referrer");
        $this->assertEquals($numVisitors['M'] + $numVisitors['F'] + $numVisitors['X'], sizeof($bookingSummary->guests), "guests");
        $this->assertEquals(sizeof($dates), sizeof($bookingSummary->bookingDates), "bookingDates");
        foreach ( $dates as $dt ) {
            $this->assert(in_array(DateTime::createFromFormat('!d.m.Y', $dt), $bookingSummary->bookingDates), "Expecting $dt");
        }
    }

    /**
     * Returns the value by XPath given an XML document.
     * $xpath : XPath ref as String (e.g. //node[@attrib='val1'])
     * $xml : XML document as String
     * Returns evaluated XPath expression
     */
    function queryByXPath($xpath, $xml) {
        // create a DOM document and load the XML data
        /*
        $xml_doc = new DomDocument;
        $xml_doc->loadXML($xml);
        $xp = new DOMXpath($xml_doc);
        return $xp->query($xpath);
        */
        $doc = simplexml_load_string($xml);
        return $doc->xpath($xpath);
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/run_unit_tests_content.xsl';
    }
}

?>
