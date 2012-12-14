<?php

/**
 * Execute the ajax controller if we are performing an ajax action.
 */    
if ( isset( $_POST['ajax_action'] ) ) {
    $ax = new AjaxController();
    $ax->handle_request($_POST['ajax_action']);
}

    
/**
 * Handler for AJAX requests.
 */
class AjaxController {

    /**
     * Default constructor.
     */    
    function AjaxController() {
        session_start();  // continue current session
    }

    /**
     * Handles the specified action.
     * $action : action constant to be handled
     */
    function handle_request($action) {

        switch ( $action ) :

            case  'SAVE_BOOKING':
                $this->save_booking();
                break;

            // enable editing of the fields in the current resource row
            case  'EDIT_RESOURCE':
                $this->edit_resource();
                break;

            // save the editing fields in the current resource row
            case  'SAVE_RESOURCE':
                $this->save_resource();
                break;

            // delete the current resource row
            case  'DELETE_RESOURCE':
                $this->delete_resource();
                break;

            // insert allocations as part of a booking
            case  'ADD_ALLOCATION':
                $this->add_booking_allocation();
                break;
            
            // enable editing of the fields in the current allocation row
            case  'EDIT_ALLOCATION':
                $this->edit_allocation();
                break;

            // save the editing fields in the current allocation row
            case  'SAVE_ALLOCATION':
                $this->save_allocation();
                break;

            // delete the current allocation row
            case  'DELETE_ALLOCATION':
                $this->delete_allocation();
                break;

            // toggle the gender of an allocation
            case  'TOGGLE_GENDER':
                $this->toggle_gender();
                break;

            // toggle the state of a booking date in the availability table
            case  'TOGGLE_BOOKING_DATE':
                $this->toggle_booking_date();
                break;
            
            // toggle the checkout state of a booking date in the (edit booking) availability table
            case  'TOGGLE_CHECKOUT_ON_BOOKING_DATE':
                $this->toggle_checkout_on_booking_date();
                break;
        
            // toggle the checkout state of an allocation from the allocation view
            case  'TOGGLE_CHECKOUT_FOR_ALLOCATION':
                $this->toggle_checkout_for_allocation();
                break;
            
            // toggle the checkout state of a booking from the booking view
            case  'TOGGLE_CHECKOUT_FOR_BOOKING':
                $this->toggle_checkout_for_booking();
                break;
            
            case  'PAGE_AVAILABILITY_TABLE_LEFT_RIGHT':
                $this->page_availability_table_left_right();
                break;
            
            // add a comment to the current booking
            case  'ADD_BOOKING_COMMENT':
                $this->add_booking_comment();
                break;

            case  'SELECT_DAILY_SUMMARY_DAY':
                $this->select_daily_summary_day();
                break;

            default:
                error_log("ERROR: Undefined AJAX action  $action");

        endswitch;
        die();
    }

    /**
     * Validates and saves a new/existing booking to the database.
     * If validation fails, nothing is saved and an error message is displayed.
     * Requires POST variables:
     *   firstname : first name
     *   lastname : surname
     *   referrer : method of booking
     */
    function save_booking() {

error_log("ajax save_booking : begin");
        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->firstname = $_POST['firstname'];
            $booking->lastname = $_POST['lastname'];
            $booking->referrer = $_POST['referrer'];
            $booking->depositPaid = floatval($_POST['deposit_paid']);
            $booking->amountToPay = floatval($_POST['amount_to_pay']);
        } else {
            throw new Exception("Session expired.");
        }
error_log("ajax save_booking : pre validate");

        // validate form
        $errors = $booking->doValidate();
        if(sizeof($errors) > 0) {
            // FIXME : can we highlight the row(s) in question?
            $error_text = '';
            foreach ($errors as $error) {
                $error_text .= $error . '<br>';
            }

            ?> <script type="text/javascript">
                document.getElementById('submitting').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo $error_text; ?></div>';
                jQuery("#submitting")
                    .css( {'color' : 'red'} );
            </script>
            <?php
            return;
        }
    
error_log("ajax save_booking : validate OK, doing SAVE");
        // validates ok, save to db
        try {
            $booking->save();
error_log("ajax save_booking : SAVE complete");
            $msg = "Updated successfully";
        } catch(DatabaseException $ex) {
            $msg = $ex->getMessage() . ". Changes were not saved.";
        } catch(AllocationException $ex) {
            $msg = $ex->getMessage() . ". Changes were not saved.";
        }
error_log("db save: $msg"); 

        // stop and redirect
        ?> <script type="text/javascript">
                var msg = "<?php echo $msg; ?>";
                document.getElementById('submitting').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;>' + msg + '</div>';
                jQuery("#submitting")
                    .css( {'color' : 'red'} );
                
                // reload allocation table; invalid rows will be highlighted
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
                // update comments
                document.getElementById('comment_log').innerHTML = <?php echo json_encode($booking->getCommentLogHtml()); ?>;
                jQuery('#deposit_paid').val("<?php echo $booking->depositPaid; ?>");
                jQuery('#amount_to_pay').val("<?php echo $booking->amountToPay; ?>");
//           jQuery('#submitting').fadeOut(5000);
//           location.href='admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ;?>wpdev-booking&booking_type=1&booking_id_selection=<?php echo  $my_booking_id;?>';
           </script>
        <?php
    }

    /**
     * Permits editing of the currently selected resource row.
     * Requires POST variables:
     *   resource_id : id of resource to edit
     */
    function edit_resource() {
        $resourceId = $_POST['resource_id'];
error_log("edit_resource $resourceId");
        $resources = new Resources($resourceId);

        ?> 
        <script type="text/javascript">
            document.getElementById('wpdev-bookingresources-content').innerHTML = <?php echo json_encode($resources->toHtml()); ?>;
        </script>
        <?php
    }

    /**
     * Deletes the selected resource row. Error is displayed if resource is still associated with any allocations.
     * Requires POST variables:
     *   resource_id : id of resource to delete
     */
    function delete_resource() {
        $resourceId = $_POST['resource_id'];
error_log("delete_resource $resourceId");

        try {
            ResourceDBO::deleteResource($resourceId);

        } catch (DatabaseException $de) {
            $msg = $de->getMessage();
        }
    
        $resources = new Resources();
        if (isset($msg)) {
            $resources->errorMessage = $msg;
        }

        ?> 
        <script type="text/javascript">
            document.getElementById('wpdev-bookingresources-content').innerHTML = <?php echo json_encode($resources->toHtml()); ?>;
        </script>
        <?php
    }

    /**
     * Saves the selected resource row.
     * Requires POST variables:
     *   resource_id : id of resource to save
     *   resource_name : name of resource
     */
    function save_resource() {

        $resourceId = $_POST['resource_id'];
        $resourceName = $_POST['resource_name'];

error_log("save_resource $resourceId $resourceName");

        if ($resourceName != '') {
            try {
                ResourceDBO::editResource($resourceId, $resourceName);

            } catch (DatabaseException $de) {
                $msg = $de->getMessage();
            }
        }
        
        $resources = new Resources();
        if (isset($msg)) {
            $resources->errorMessage = $msg;
        }

        ?> 
        <script type="text/javascript">
            document.getElementById('wpdev-bookingresources-content').innerHTML = <?php echo json_encode($resources->toHtml()); ?>;
        </script>
        <?php
    }

    /**
     * Adds a number of new allocations to the current booking.
     * Requires POST variables:
     *   firstname : first name
     *   num_visitors : number of guests to allocate
     *   gender : gender of guests to allocate
     *   dates : comma delimited list of dates for allocation (dd.MM.yyyy)
     *   booking_resource : id of resource to allocate to
     *   resource_property : comma delimited list of resource property ids to allocate to
     */
    function add_booking_allocation() {

        $firstname = $_POST['firstname'];
        $num_guests['M'] = empty($_POST['num_guests_m']) ? 0 : intval($_POST['num_guests_m']);
        $num_guests['F'] = empty($_POST['num_guests_f']) ? 0 : intval($_POST['num_guests_f']);
        $num_guests['X'] = empty($_POST['num_guests_x']) ? 0 : intval($_POST['num_guests_x']);
        $reqRoomSize = $_POST['req_room_size'];
        $reqRoomType = $_POST['req_room_type'];
        $dates = $_POST['dates'];
        $res = $_POST['booking_resource'];
        $resource_property = trim($_POST['resource_property'], " ,");

        // keep allocations in a datastructure saved to session
        // { allocation_id, resource_id, array[dates] }
        // display datastructure(s) as table from min(dates) for 2 weeks afterwards
        // editing table on screen updates datastructure in real-time
        // on submit, start transaction, validate allocations, save and end transaction

        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->firstname = $firstname;
            try {
                $booking->addAllocation($num_guests, $res == "0" ? null : $res, 
                    $reqRoomSize, $reqRoomType,
                    explode(",", trim($dates, " ,")), 
                    empty($resource_property) ? null : explode(",", $resource_property));

            } catch (AllocationException $ae) {
                ?> 
                <script type="text/javascript">
                    document.getElementById('ajax_respond').innerHTML = <?php echo json_encode($ae->getMessage()); ?>;
                    jQuery("#ajax_respond")
                        .css( {'color' : 'red'} );
                </script>
                <?php
                return;
            }
        } else {
            ?> 
            <script type="text/javascript">
                document.getElementById('ajax_respond').innerHTML = '<?php echo "Session has expired. Please reload the page to continue."; ?><br>';
            </script>
            <?php
            return;
        }

        ?> 
           <script type="text/javascript">
              document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
           </script>
        <?php
    }

    /**
     * Enables the editing fields for the given allocation.
     * Requires POST variables:
     *   rowid : id of row to edit in allocation table
     */
    function edit_allocation() {
        $rowid = $_POST['rowid'];

        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->enableEditOnAllocation($rowid);
            ?> 
            <script type="text/javascript">
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
            </script>
            <?php
        }
    }

    /**
     * Saves the fields for the given allocation.
     * Requires POST variables:
     *   rowid : id of row in allocation table to save
     *   resource_id : id of resource to allocate to
     *   allocation_name : name of guest for allocation
     */
    function save_allocation() {
        $rowid = $_POST['rowid'];
        $resourceId = $_POST['resource_id'];
        $name = $_POST['allocation_name'];
error_log(var_export($_POST, true));

        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->updateAllocationRow($rowid, $name, $resourceId);
            $booking->disableEditOnAllocation($rowid);
            ?> 
            <script type="text/javascript">
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
            </script>
            <?php
        }
    }

    /**
     * Deletes the specified allocation row.
     * Requires POST variables:
     *   rowid : id of row in allocation table to remove
     */
    function delete_allocation() {
        $rowid = $_POST['rowid'];

        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->deleteAllocationRow($rowid);
            ?> 
            <script type="text/javascript">
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
            </script>
            <?php
        }
    }

    /**
     * Toggles the status of the allocation on the given booking date.
     * Requires POST variables:
     *   rowid : id of row in allocation table we are modifying
     *   booking_date : date (dd.MM.yyyy) we are toggling the status for
     */
    function toggle_booking_date() {
        $rowid = $_POST['rowid'];
        $dt = $_POST['booking_date'];

        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->toggleBookingStateAt($rowid, $dt);
            ?> 
            <script type="text/javascript">
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
            </script>
            <?php
        }
    }

    /**
     * Toggles the checkout status of the allocation on the given booking date.
     * Requires POST variables:
     *   rowid : id of row in allocation table we are modifying
     *   booking_date : date (dd.MM.yyyy) to toggle checkout on
     */
    function toggle_checkout_on_booking_date() {
        $rowid = $_POST['rowid'];
        $dt = $_POST['booking_date'];

        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->toggleCheckoutOnBookingDate($rowid, $dt);
            ?> 
            <script type="text/javascript">
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
            </script>
            <?php
        }
    }

    /**
     * Toggles the checkout status of the allocation on the allocation view.
     * Requires POST variables:
     *   resource_id : id of resource to be modified
     *   allocation_id : id of allocation to toggle checkout status for
     *   posn : 0-based position in allocation view to checkout
     */
    function toggle_checkout_for_allocation() {
        $resourceId = $_POST['resource_id'];
        $allocationId = $_POST['allocation_id'];
        $posn = $_POST['posn'];

error_log('begin TOGGLE_CHECKOUT_FOR_ALLOCATION');
        if(isset($_SESSION['ALLOCATION_VIEW'])) {
            $av = $_SESSION['ALLOCATION_VIEW'];
            $av->toggleCheckoutOnBookingDate($allocationId, $posn);
        
            // create a new allocation view for the updated resource
            $viewForResource = new AllocationViewResource($resourceId, $av->showMinDate, $av->showMaxDate);
            $viewForResource->doSearch();
        
            ?> 
            <script type="text/javascript">
                document.getElementById('table_resource_<?php echo $resourceId;?>').innerHTML = <?php echo json_encode($viewForResource->toHtml()); ?>;
            </script>
            <?php
error_log('end TOGGLE_CHECKOUT_FOR_ALLOCATION');
        }
    }

    /**
     * Sets all applicable allocations on the specified booking to checkedout.
     * Requires POST variables:
     *   booking_id : id of booking to checkout
     */
    function toggle_checkout_for_booking() {
        $bookingId = $_POST['booking_id'];

error_log('begin TOGGLE_CHECKOUT_FOR_BOOKING '.$bookingId);
        if(isset($_SESSION['BOOKING_VIEW'])) {
            $bv = $_SESSION['BOOKING_VIEW'];
            $summary = $bv->doCheckoutForBooking($bookingId);
        
            if ($summary != null) {
                ?> 
                <script type="text/javascript">
                    document.getElementById('booking_row_<?php echo $bookingId;?>').innerHTML = <?php echo json_encode($summary->toHtml()); ?>;
                </script>
                <?php
            }
error_log('end TOGGLE_CHECKOUT_FOR_BOOKING '.$bookingId);
        }
    }

    /**
     * Shifts the allocation view calendar to the right or to the left.
     * Requires POST variables:
     *   direction : one of "left", "right"
     */
    function page_availability_table_left_right() {
        $direction = $_POST['direction'];
        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            if ($direction == "right") {
                $booking->shiftCalendarRight();
            } else {
                $booking->shiftCalendarLeft();
            }
            ?> 
            <script type="text/javascript">
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
            </script>
            <?php
        }
    }

    /**
     * Adds a comment to the current booking.
     * Requires POST variables:
     *   booking_comment : comment to leave
     */
    function add_booking_comment() {
        $comment = $_POST['booking_comment'];
        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->addComment($comment, BookingComment::COMMENT_TYPE_USER);
            ?> 
            <script type="text/javascript">
                document.getElementById('comment_log').innerHTML = <?php echo json_encode($booking->getCommentLogHtml()); ?>;
                document.getElementById('booking_comment').value = '';
            </script>
            <?php
        }
    }

    /**
     * Toggles gender for an allocation.
     * Requires POST variables:
     *   row_id : id of allocation row to toggle
     */
    function toggle_gender() {
        $rowId = $_POST['rowid'];
        if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
            $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
            $booking->toggleGender($rowId);
            ?> 
            <script type="text/javascript">
                // reload allocation table
                document.getElementById('booking_allocations').innerHTML = <?php echo json_encode($booking->getAllocationTableHtml()); ?>;
            </script>
            <?php
        }
    }

    /**
     * User updates the date to show for the daily summary. Update dependent data tables.
     * Requires POST variables:
     *   calendar_selected_date : date (dd.MM.yyyy) to update daily summary for
     */
    function select_daily_summary_day() {

        $selectedDate = DateTime::createFromFormat('!d.m.Y', $_POST['calendar_selected_date'], new DateTimeZone('UTC'));
        $ds = new DailySummaryData($selectedDate);
        $ds->doSummaryUpdate();
    
        ?> 
        <script type="text/javascript">
            document.getElementById('daily_summary_contents').innerHTML = <?php echo json_encode($ds->toHtml()); ?>;
        </script>
        <?php
    }
}

?>
