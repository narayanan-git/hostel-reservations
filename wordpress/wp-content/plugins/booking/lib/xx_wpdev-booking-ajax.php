<?php

if (  (! isset( $_GET['merchant_return_link'] ) ) && (! isset( $_GET['payed_booking'] ) ) && (!function_exists ('get_option')  )  ) { die('You do not have permission to direct access to this file !!!'); }


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Getting Ajax requests
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if ( isset( $_POST['ajax_action'] ) ) {
    define('DOING_AJAX', true);


    if ( class_exists('wpdev_bk_personal')) { $wpdev_bk_personal_in_ajax = new wpdev_bk_personal(); }

    wpdev_bk_ajax_responder();
}


if ( ( isset( $_GET['payed_booking'] ) )  || (  isset( $_GET['merchant_return_link'])) ) {

    if ( class_exists('wpdev_bk_personal'))                 { $wpdev_bk_personal_in_ajax = new wpdev_bk_personal(); }

    if (function_exists ('wpdev_bk_update_pay_status')) wpdev_bk_update_pay_status();
    die;
} /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//    A J A X     R e s p o n d e r     Real Ajax with jQuery sender     ///////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpdev_bk_ajax_responder() {

    session_start();  // continue current session
    global $wpdb;
    $action = $_POST['ajax_action'];

    if  (isset($_POST['wpdev_active_locale'])) {    // Reload locale according request parameter
            global  $l10n;
            if (isset($l10n['wpdev-booking'])) unset($l10n['wpdev-booking']);

            if(! defined('WPDEV_BK_LOCALE_RELOAD') ) define('WPDEV_BK_LOCALE_RELOAD', $_POST['wpdev_active_locale']);
          
            loadLocale(WPDEV_BK_LOCALE_RELOAD);
    }

    switch ( $action ) :

        case  'SAVE_BOOKING':
            wpdev_bk_insert_new_booking_v2();
            die();
            break;

/////////////////////// BEGIN CUSTOM CODE /////////////////////////
        // enable editing of the fields in the current resource row
        case  'EDIT_RESOURCE':
            wpdev_edit_resource();
            die();
            break;

        // save the editing fields in the current resource row
        case  'SAVE_RESOURCE':
            wpdev_save_resource();
            die();
            break;

        // delete the current resource row
        case  'DELETE_RESOURCE':
            wpdev_delete_resource();
            die();
            break;

        // insert allocations as part of a booking
        case  'ADD_ALLOCATION':
            wpdev_add_booking_allocation();
            die();
            break;
            
        // enable editing of the fields in the current allocation row
        case  'EDIT_ALLOCATION':
            wpdev_edit_allocation();
            die();
            break;

        // save the editing fields in the current allocation row
        case  'SAVE_ALLOCATION':
            wpdev_save_allocation();
            die();
            break;

        // delete the current allocation row
        case  'DELETE_ALLOCATION':
            wpdev_delete_allocation();
            die();
            break;

        // toggle the state of a booking date in the availability table
        case  'TOGGLE_BOOKING_DATE':
            wpdev_toggle_booking_date();
            die();
            break;
            
        // toggle the checkout state of a booking date in the (edit booking) availability table
        case  'TOGGLE_CHECKOUT_ON_BOOKING_DATE':
            wpdev_toggle_checkout_on_booking_date();
            die();
            break;
        
        // toggle the checkout state of an allocation from the allocation view
        case  'TOGGLE_CHECKOUT_FOR_ALLOCATION':
            wpdev_toggle_checkout_for_allocation();
            die();
            break;
            
        case  'PAGE_AVAILABILITY_TABLE_LEFT_RIGHT':
            wpdev_page_availability_table_left_right();
            die();
            break;
            
        // add a comment to the current booking
        case  'ADD_BOOKING_COMMENT':
            wpdev_add_booking_comment();
            die();
            break;

        case  'SELECT_DAILY_SUMMARY_DAY':
            wpdev_select_daily_summary_day();
            die();
            break;

/////////////////////// END CUSTOM CODE ///////////////////////////

            case 'UPDATE_READ_UNREAD':

            make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

            $is_read_or_unread = $_POST[ "is_read_or_unread" ];
            if ($is_read_or_unread == 1)   $is_new = '1';
            else                           $is_new = '0';

            $id_of_new_bookings       = $_POST[ "booking_id" ];
            $arrayof_bookings_id    = explode('|',$id_of_new_bookings);

            renew_NumOfNewBookings(  $arrayof_bookings_id, $is_new  );


            ?>  <script type="text/javascript">
                    <?php foreach ($arrayof_bookings_id as $bk_id) {
                            if ($is_new == '1') { ?>
                                set_booking_row_read(<?php echo $bk_id ?>);
                            <?php } else { ?>
                                set_booking_row_unread(<?php echo $bk_id ?>);
                            <?php }?>
                    <?php } ?>
                    <?php if ($is_new == '1') { ?>
                    //    var my_num = parseInt(jQuery('.bk-update-count').text()) + parseInt(1<?php echo '*' . count($arrayof_bookings_id); ?>);
                    <?php } else { ?>
                    //    var my_num = parseInt(jQuery('.bk-update-count').text()) - parseInt(1<?php echo '*' . count($arrayof_bookings_id); ?>);
                    <?php } ?>
                    //jQuery('.bk-update-count').html( my_num );
                    document.getElementById('ajax_message').innerHTML = '<?php if ($is_new == '1') { echo __('Set as Read', 'wpdev-booking'); } else { echo __('Set as Unread', 'wpdev-booking'); } ?>';
                    jQuery('#ajax_message').fadeOut(1000);
                </script> <?php
            die();

            break;

        case 'UPDATE_APPROVE' :

            make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

            // Approve or Unapprove
            $is_approve_or_pending = $_POST[ "is_approve_or_pending" ];
            if ($is_approve_or_pending == 1)   $is_approve_or_pending = '1';
            else                        $is_approve_or_pending = '0';
            // Booking ID
            $booking_id       = $_POST[ "booking_id" ];
            $approved_id    = explode('|',$booking_id);
            $denyreason     = $_POST["denyreason"];
            $is_send_emeils = $_POST["is_send_emeils"];
            

            if ( (count($approved_id)>0) && ($approved_id !==false)) {

                $approved_id_str = join( ',', $approved_id);

                if ( false === $wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix ."bookingdates SET approved = '".$is_approve_or_pending."' WHERE booking_id IN ($approved_id_str)") ) ){
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating to DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }

                if ($is_approve_or_pending == '1')
                    sendApproveEmails($approved_id_str, $is_send_emeils);
                else
                    sendDeclineEmails($approved_id_str, $is_send_emeils,$denyreason);

                ?>  <script type="text/javascript">
                        <?php foreach ($approved_id as $bk_id) {
                                if ($is_approve_or_pending == '1') { ?>
                                    set_booking_row_approved(<?php echo $bk_id ?>);
                                <?php } else { ?>
                                    set_booking_row_pending(<?php echo $bk_id ?>);
                                <?php }?>
                        <?php } ?>
                        document.getElementById('ajax_message').innerHTML = '<?php if ($is_approve_or_pending == '1') { echo __('Set as Approved', 'wpdev-booking'); } else { echo __('Set as Pending', 'wpdev-booking'); } ?>';
                        jQuery('#ajax_message').fadeOut(1000);
                    </script> <?php
                die();
            }
            break;

        case 'DELETE_APPROVE' :
            make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

            $booking_id       = $_POST[ "booking_id" ];         // Booking ID
            $denyreason     = $_POST["denyreason"];
            if ( ( $denyreason == __('Reason of cancellation here', 'wpdev-booking')) || ( $denyreason == 'Reason of cancel here') )  $denyreason = '';
            $is_send_emeils = $_POST["is_send_emeils"];
            $approved_id    = explode('|',$booking_id);

            if ( (count($approved_id)>0) && ($approved_id !=false) && ($approved_id !='')) {

                $approved_id_str = join( ',', $approved_id);

                sendDeclineEmails($approved_id_str, $is_send_emeils,$denyreason);


                if ( false === $wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->prefix ."bookingdates WHERE booking_id IN ($approved_id_str)") ) ){
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting dates at DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }

                if ( false === $wpdb->query($wpdb->prepare( "DELETE FROM ".$wpdb->prefix ."booking WHERE booking_id IN ($approved_id_str)") ) ){
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting reservation at DB',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                    die();
                }

                ?>
                    <script type="text/javascript">
                        <?php foreach ($approved_id as $bk_id) { ?>
                                    set_booking_row_deleted(<?php echo $bk_id ?>);
                        <?php } ?>
                        document.getElementById('ajax_message').innerHTML = '<?php echo __('Deleted', 'wpdev-booking'); ?>';
                        jQuery('#ajax_message').fadeOut(1000);
                    </script>
                <?php
                die();
            }
            break;

        case 'DELETE_BY_VISITOR':
            make_bk_action('wpdev_delete_booking_by_visitor');
            break;

        case 'SAVE_BK_COST':
            make_bk_action('wpdev_save_bk_cost');
            break;

        case 'SEND_PAYMENT_REQUEST':
            make_bk_action('wpdev_send_payment_request');
            break;


        case 'CHANGE_PAYMENT_STATUS':
            make_bk_action('wpdev_change_payment_status');
            break;

        case 'UPDATE_BK_RESOURCE_4_BOOKING':
            make_bk_action('wpdev_updating_bk_resource_of_booking');
            break;


        case 'UPDATE_REMARK':
            make_bk_action('wpdev_updating_remark');
            break;

        case 'DELETE_BK_FORM':
            make_bk_action('wpdev_delete_booking_form');
            break;

        case 'USER_SAVE_OPTION':

            if ($_POST['option'] == 'ADMIN_CALENDAR_COUNT') {
                update_user_option($_POST['user_id'],'booking_admin_calendar_count',$_POST['count']);
            }
            ?> <script type="text/javascript">
                    document.getElementById('ajax_message').innerHTML = '<?php echo __('Done', 'wpdev-booking'); ?>';
                    jQuery('#ajax_message').fadeOut(1000);
                    <?php if ( $_POST['is_reload'] == 1 ) { ?> location.reload(true); <?php  } ?>
                </script> <?php
            die();
            break;

        case 'USER_SAVE_WINDOW_STATE':
            update_user_option($_POST['user_id'],'booking_win_' . $_POST['window'] ,$_POST['is_closed']);
            die();
            break;

        case 'CALCULATE_THE_COST':
            make_bk_action('wpdev_ajax_show_cost');
            die();
            break;

        case 'BOOKING_SEARCH':
            make_bk_action('wpdev_ajax_booking_search');
            die();
            break;

        case 'CHECK_BK_NEWS':
            wpdev_ajax_check_bk_news();
            die();
            break;
        case 'CHECK_BK_VERSION':
            wpdev_ajax_check_bk_version();
            die();
            break;

        case 'SAVE_BK_LISTING_FILTER':
            make_bk_action('wpdev_ajax_save_bk_listing_filter');
            die();
            break;
        case 'EXPORT_BOOKINGS_TO_CSV'    :
            make_bk_action('wpdev_ajax_export_bookings_to_csv');
            die();

        default:
            if (function_exists ('wpdev_pro_bk_ajax')) wpdev_pro_bk_ajax();
            error_log("ERROR: Undefined AJAX action  $action");
            die();

        endswitch;
}


///////////////////////// BEGIN CUSTOM CODE //////////////////////////////
function wpdev_bk_insert_new_booking_v2(){

error_log("wpdev_bk_insert_new_booking_v2 : begin");
    if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
        $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
        $booking->firstname = $_POST['firstname'];
        $booking->lastname = $_POST['lastname'];
        $booking->referrer = $_POST['referrer'];
    } 
error_log("wpdev_bk_insert_new_booking_v2 : pre validate");

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
                .css( {'color' : 'red'} )
        </script>
        <?php
        return;
    }
    
error_log("wpdev_bk_insert_new_booking_v2 : validate OK, doing SAVE");
    // validates ok, save to db
    try {
        $booking->save();
error_log("wpdev_bk_insert_new_booking_v2 : SAVE complete");
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
//           jQuery('#submitting').fadeOut(5000);
//           location.href='admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ;?>wpdev-booking&booking_type=1&booking_id_selection=<?php echo  $my_booking_id;?>';
       </script>
    <?php
}

/**
 * Permits editing of the currently selected resource row.
 */
function wpdev_edit_resource() {
    $resourceId = $_POST['resource_id'];
error_log("wpdev_edit_resource $resourceId");
    $resources = new Resources($resourceId);

    ?> 
    <script type="text/javascript">
        document.getElementById('wpdev-bookingresources-content').innerHTML = <?php echo json_encode($resources->toHtml()); ?>;
    </script>
    <?php
}

/**
 * Deletes the selected resource row.
 */
function wpdev_delete_resource() {
    $resourceId = $_POST['resource_id'];
error_log("wpdev_delete_resource $resourceId");

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
 */
function wpdev_save_resource() {

    $resourceId = $_POST['resource_id'];
    $resourceName = $_POST['resource_name'];

error_log("wpdev_save_resource $resourceId $resourceName");

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
 */
function wpdev_add_booking_allocation() {

    $firstname = $_POST['firstname'];
    $num_visitors = $_POST['num_visitors'];
    $gender = $_POST['gender'];
    $dates = $_POST['dates'];
    $res = $_POST['booking_resource'];
    $room_type = $_POST['room_type'];
    $resource_property = $_POST['resource_property'];

    // keep allocations in a datastructure saved to session
    // { allocation_id, resource_id, gender, array[dates] }
    // display datastructure(s) as table from min(dates) for 2 weeks afterwards
    // editing table on screen updates datastructure in real-time
    // on submit, start transaction, validate allocations, save and end transaction

    if(isset($_SESSION['ADD_BOOKING_CONTROLLER'])) {
        $booking = $_SESSION['ADD_BOOKING_CONTROLLER'];
        $booking->firstname = $firstname;
        try {
            $booking->addAllocation($num_visitors, $gender, $res, $dates);

        } catch (AllocationException $ae) {
            ?> 
            <script type="text/javascript">
                document.getElementById('ajax_respond').innerHTML = "There is not enough availability for the room (type) and dates chosen.";
                jQuery("#ajax_respond")
                    .css( {'color' : 'red'} );
            </script>
            <?php
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
 */
function wpdev_edit_allocation() {
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
 */
function wpdev_save_allocation() {
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
 */
function wpdev_delete_allocation() {
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
 */
function wpdev_toggle_booking_date() {
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
 */
function wpdev_toggle_checkout_on_booking_date() {
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
 */
function wpdev_toggle_checkout_for_allocation() {
    $resourceId = $_POST['resource_id'];
    $allocationId = $_POST['allocation_id'];
    $posn = $_POST['posn'];

error_log('begin TOGGLE_CHECKOUT_FOR_ALLOCATION');
    if(isset($_SESSION['ALLOCATION_VIEW'])) {
        $av = $_SESSION['ALLOCATION_VIEW'];
error_log('begin db write');
        $av->toggleCheckoutOnBookingDate($allocationId, $posn);
error_log('end db write');
        
        // create a new allocation view for the updated resource
error_log('begin search');
        $viewForResource = new AllocationViewResource($resourceId, $av->showMinDate, $av->showMaxDate);
        $viewForResource->doSearch();
error_log('end search');
        
        ?> 
        <script type="text/javascript">
            document.getElementById('table_resource_<?php echo $resourceId;?>').innerHTML = <?php echo json_encode($viewForResource->toHtml()); ?>;
        </script>
        <?php
error_log('end TOGGLE_CHECKOUT_FOR_ALLOCATION');
    }
}

/**
 * Shifts the allocation view calendar to the right or to the left.
 */
function wpdev_page_availability_table_left_right() {
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
 */
function wpdev_add_booking_comment() {
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
 * User updates the date to show for the daily summary. Update dependent data tables.
 */
function wpdev_select_daily_summary_day() {

    $selectedDate = DateTime::createFromFormat('!d.m.Y', $_POST['calendar_selected_date'], new DateTimeZone('UTC'));
    $ds = new DailySummaryData($selectedDate);
    $ds->doSummaryUpdate();
    
    ?> 
    <script type="text/javascript">
        document.getElementById('daily_summary_contents').innerHTML = <?php echo json_encode($ds->toHtml()); ?>;
    </script>
    <?php
}

//////////////////////////////////////////////////////////////////////////

function wpdev_bk_insert_new_booking(){


            make_bk_action('check_multiuser_params_for_client_side', $_POST[  "bktype" ] );

            global $wpdb;
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Define init variables
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            $dates          = $_POST[ "dates" ];

            $bktype         = $_POST[ "bktype" ];
            $formdata       = $_POST[ "form" ];
            $formdata = escape_any_xss($formdata);
            $is_send_emeils = 1;        if (isset($_POST["is_send_emeils"])) $is_send_emeils = $_POST["is_send_emeils"];

            $my_booking_id  = 0;
            $my_booking_hash= '';

            if (isset($_POST['my_booking_hash'])) {
                $my_booking_hash = $_POST['my_booking_hash'];
                if ($my_booking_hash!='') {
                    $my_booking_id_type = false;
                    $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $my_booking_hash);
                    if ($my_booking_id_type !== false) {
                        $my_booking_id = $my_booking_id_type[0];
                        $bktype        = $my_booking_id_type[1];
                    }
                }
            }



            if (strpos($dates,' - ')!== FALSE) {
                $dates =explode(' - ', $dates );
                $dates = createDateRangeArray($dates[0],$dates[1]);
            }

            ///  CAPTCHA CHECKING   //////////////////////////////////////////////////////////////////////////////////////
            $the_answer_from_respondent = $_POST['captcha_user_input'];
            $prefix = $_POST['captcha_chalange'];
            if (! ( ($the_answer_from_respondent == '') && ($prefix == '') )) {
                $captcha_instance = new wpdevReallySimpleCaptcha();
                $correct = $captcha_instance->check($prefix, $the_answer_from_respondent);

                if (! $correct) {
                    $word = $captcha_instance->generate_random_word();
                    $prefix = mt_rand();
                    $captcha_instance->generate_image($prefix, $word);

                    $filename = $prefix . '.png';
                    $captcha_url = WPDEV_BK_PLUGIN_URL . '/js/captcha/tmp/' .$filename;
                    $ref = substr($filename, 0, strrpos($filename, '.'));
                    ?> <script type="text/javascript">
                        document.getElementById('captcha_input<?php echo $bktype; ?>').value = '';
                        // chnage img
                        document.getElementById('captcha_img<?php echo $bktype; ?>').src = '<?php echo $captcha_url; ?>';
                        document.getElementById('wpdev_captcha_challenge_<?php echo $bktype; ?>').value = '<?php echo $ref; ?>';
                        document.getElementById('captcha_msg<?php echo $bktype; ?>').innerHTML =
                            '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo __('Your entered code is incorrect', 'wpdev-booking'); ?></div>';
                        document.getElementById('submiting<?php echo $bktype; ?>').innerHTML ='';
                        jQuery('#captcha_input<?php echo $bktype; ?>')
                        .fadeOut( 350 ).fadeIn( 300 )
                        .fadeOut( 350 ).fadeIn( 400 )
                        .animate( {opacity: 1}, 4000 )
                        ;  // mark red border
                        jQuery(".wpdev-help-message div")
                        .css( {'color' : 'red'} )
                        .animate( {opacity: 1}, 10000 )
                        .fadeOut( 2000 );   // hide message
                        document.getElementById('captcha_input<?php echo $bktype; ?>').focus();    // make focus to elemnt

                    </script> <?php
                    die();
                }
            }//////////////////////////////////////////////////////////////////////////////////////////////////////////

            $booking_form_show = get_form_content ($formdata, $bktype);
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




            $my_modification_date = "'" . date_i18n( 'Y-m-d H:i:s'  ) ."'" ;    // Localize booking modification date
            $my_modification_date = 'NOW()';                                    // Server value modification date

            if ($my_booking_id>0) {                  // Edit exist booking

                if ( strpos($_SERVER['HTTP_REFERER'],'wp-admin/admin.php?') !==false ) {
                    ?> <script type="text/javascript">
                        document.getElementById('ajax_working').innerHTML =
                            '<div class="info_message ajax_message" id="ajax_message">\n\
                                            <div style="float:left;"><?php echo __('Updating...', 'wpdev-booking'); ?></div> \n\
                                            <div  style="float:left;width:80px;margin-top:-3px;">\n\
                                                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                                            </div>\n\
                                        </div>';
                    </script> <?php
                }
                $update_sql = "UPDATE ".$wpdb->prefix ."booking AS bk SET bk.form='$formdata', bk.booking_type=$bktype , bk.modification_date=".$my_modification_date." WHERE bk.booking_id=$my_booking_id;";
                if ( false === $wpdb->query($wpdb->prepare( $update_sql ) ) ){
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating exist booking in BD',__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }

                // Check if dates already aproved or no
                $slct_sql = "SELECT approved FROM ".$wpdb->prefix ."bookingdates WHERE booking_id IN ($my_booking_id) LIMIT 0,1";
                $slct_sql_results  = $wpdb->get_results( $wpdb->prepare($slct_sql) );
                if ( count($slct_sql_results) > 0 ) {
                    $is_approved_dates = $slct_sql_results[0]->approved;
                }

                $delete_sql = "DELETE FROM ".$wpdb->prefix ."bookingdates WHERE booking_id IN ($my_booking_id)";
                if ( false === $wpdb->query($wpdb->prepare( $delete_sql ) ) ){
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating exist booking for deleting dates in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }
                $booking_id = (int) $my_booking_id;       //Get ID  of reservation

            } else {                                // Add new booking

                $sql_insertion = "INSERT INTO ".$wpdb->prefix ."booking (form, booking_type, modification_date) VALUES ('$formdata',  $bktype, ".$my_modification_date." )" ;
//debuge($formdata);
                if ( false === $wpdb->query($wpdb->prepare( $sql_insertion ) ) ){
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during inserting into BD',__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }
                // Make insertion into BOOKINGDATES
                $booking_id = (int) $wpdb->insert_id;       //Get ID  of reservation

                $is_approved_dates = '0';
                $auto_approve_new_bookings_is_active       =  get_bk_option( 'booking_auto_approve_new_bookings_is_active' );
                if ( trim($auto_approve_new_bookings_is_active) == 'On')
                    $is_approved_dates = '1';


            }




            $sdform = $_POST['form'];
            $my_dates = explode(",",$dates);
            $i=0; foreach ($my_dates as $md) {$my_dates[$i] = trim($my_dates[$i]) ; $i++; }

            $start_end_time = get_times_from_bk_form($sdform, $my_dates, $bktype);
            $start_time = $start_end_time[0];
            $end_time = $start_end_time[1];
            $my_dates = $start_end_time[2];

            make_bk_action('wpdev_booking_post_inserted', $booking_id, $bktype, str_replace('|',',',$dates),  array($start_time, $end_time ) );
            $my_cost = apply_bk_filter('get_booking_cost_from_db', '', $booking_id);


            $i=0;
            foreach ($my_dates as $md) { // Set in dates in such format: yyyy.mm.dd
                if ($md != '') {
                    $md = explode('.',$md);
                    $my_dates[$i] = $md[2] . '.' . $md[1] . '.' . $md[0] ;
                } else { unset($my_dates[$i]) ; } // If some dates is empty so remove it   // This situation can be if using several bk calendars and some calendars is not checked
                $i++;

            }
            sort($my_dates); // Sort dates

            $my_dates4emeil = '';
            $i=0;
            $insert='';
            $my_date_previos = '';
            foreach ($my_dates as $my_date) {
                $i++;          // Loop through all dates
                if (strpos($my_date,'.')!==false) {

                    if ( get_bk_option( 'booking_recurrent_time' ) !== 'On') {
                            $my_date = explode('.',$my_date);
                            if ($i == 1) {
                                $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $start_time[0], $start_time[1], $start_time[2] );
                            }elseif ($i == count($my_dates)) {
                                $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $end_time[0], $end_time[1], $end_time[2] );
                            }else {
                                $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], '00', '00', '00' );
                            }
                            $my_dates4emeil .= $date . ',';
                            if ( !empty($insert) ) $insert .= ', ';
                            $insert .= "('$booking_id', '$date', '$is_approved_dates' )";
                    } else {
                            if ($my_date_previos  == $my_date) continue; // escape for single day selections.

                            $my_date_previos  = $my_date;
                            $my_date = explode('.',$my_date);
                            $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $start_time[0], $start_time[1], $start_time[2] );
                            $my_dates4emeil .= $date . ',';
                            if ( !empty($insert) ) $insert .= ', ';
                            $insert .= "('$booking_id', '$date', '$is_approved_dates' )";

                            $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $end_time[0], $end_time[1], $end_time[2] );
                            $my_dates4emeil .= $date . ',';
                            if ( !empty($insert) ) $insert .= ', ';
                            $insert .= "('$booking_id', '$date', '$is_approved_dates' )";

                    }

                }
            }
            $my_dates4emeil = substr($my_dates4emeil,0,-1);

            $my_dates4emeil_check_in_out = explode(',',$my_dates4emeil);
            $my_check_in_date = change_date_format($my_dates4emeil_check_in_out[0] );
            $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );
            /* // This add 1 more day for check out day
            $my_check_out_date = $my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ];
            $dt = trim($my_check_out_date);
            $dta = explode(' ',$dt);
            $dta = $dta[0];
            $dta = explode('-',$dta);
            $my_check_out_date = date('Y-m-d H:i:s' , mktime(0, 0, 0, $dta[1], ($dta[2]+1), $dta[0] ));
            $my_check_out_date = change_date_format($my_check_out_date);
            /**/

            if ( !empty($insert) )
                if ( false === $wpdb->query($wpdb->prepare("INSERT INTO ".$wpdb->prefix ."bookingdates (booking_id, booking_date, approved) VALUES " . $insert) ) ){
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during inserting into BD - Dates',__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }

            if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $bktype );
            else $bk_title = '';

            if ($my_booking_id>0) { // For editing exist booking


                $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_modification_adress') ) ;
                $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_modification_subject') );
                $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_modification_content') );
                $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
                $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

                if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $bktype );
                else $bk_title = '';

                $booking_form_show = get_form_content ($formdata, $bktype);

                $mail_body_to_send = str_replace('[bookingtype]', $bk_title, $mail_body);
                if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( get_dates_str($booking_id) );
                else                                                  $my_dates_4_send = change_date_format(get_dates_str($booking_id));
                $mail_body_to_send = str_replace('[dates]',$my_dates_4_send , $mail_body_to_send);
                $mail_body_to_send = str_replace('[check_in_date]',$my_check_in_date , $mail_body_to_send);
                $mail_body_to_send = str_replace('[check_out_date]',$my_check_out_date , $mail_body_to_send);
                $mail_body_to_send = str_replace('[id]', $booking_id , $mail_body_to_send);


                $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
                if (! isset($denyreason)) $denyreason = '';
                $mail_body_to_send = str_replace('[denyreason]', $denyreason, $mail_body_to_send);
                $mail_body_to_send = str_replace('[name]', $booking_form_show['name'], $mail_body_to_send);
                $mail_body_to_send = str_replace('[cost]', $my_cost, $mail_body_to_send);
                if ( isset($booking_form_show['secondname']) ) $mail_body_to_send = str_replace('[secondname]', $booking_form_show['secondname'], $mail_body_to_send);
                $mail_body_to_send = str_replace('[siteurl]', htmlspecialchars_decode( '<a href="'.site_url().'">' . site_url() . '</a>'), $mail_body_to_send);
                $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $booking_id );

                $mail_subject = str_replace('[name]', $booking_form_show['name'], $mail_subject);
                if ( isset($booking_form_show['secondname']) ) $mail_subject = str_replace('[secondname]', $booking_form_show['secondname'], $mail_subject);

                $mail_recipient =  $booking_form_show['email'];

                $mail_headers = "From: $mail_sender\n";
                $mail_headers .= "Content-Type: text/html\n";

                if (get_bk_option( 'booking_is_email_modification_adress'  ) != 'Off') {
                    // Send to the Visitor
                    if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                        if ($is_send_emeils != 0 )
                            @wp_mail($mail_recipient, $mail_subject, $mail_body_to_send, $mail_headers);

                    // Send to the Admin also
                    $mail_recipient =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );
                    $is_email_modification_send_copy_to_admin = get_bk_option( 'booking_is_email_modification_send_copy_to_admin' );
                    if ( $is_email_modification_send_copy_to_admin == 'On')
                        if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                            if ($is_send_emeils != 0 )
                                @wp_mail($mail_recipient, $mail_subject, $mail_body_to_send, $mail_headers);
                }
//debuge($_SERVER);

                if ( strpos($_SERVER['HTTP_REFERER'],'wp-admin/admin.php?') ===false ) {
                    do_action('wpdev_new_booking',$booking_id, $bktype, str_replace('|',',',$dates), array($start_time, $end_time ) ,$sdform );
                }

                ?> <script type="text/javascript">
                <?php
                if ( strpos($_SERVER['HTTP_REFERER'],'wp-admin/admin.php?') ===false ) { ?>
                            //document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div class=\"submiting_content\" ><?php echo get_bk_option( 'booking_title_after_reservation'); ?></div>';
                            //jQuery('.submiting_content').fadeOut(<?php echo get_bk_option( 'booking_title_after_reservation_time'); ?>);
                            setReservedSelectedDates('<?php echo $bktype; ?>');
                    <?php }  else { ?>
                            document.getElementById('ajax_message').innerHTML = '<?php echo __('Updated successfully', 'wpdev-booking'); ?>';
                            jQuery('#ajax_message').fadeOut(1000);
                            document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo __('Updated successfully', 'wpdev-booking'); ?></div>';
                            location.href='admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ;?>wpdev-booking&booking_type=<?php echo $bktype; ?>&booking_id_selection=<?php echo  $my_booking_id;?>';
                    <?php } ?>
                    </script> <?php


            } else {
                    if ( count($my_dates) > 0 ) {
                        //// For inserting NEW booking
                        // Sending mail ///////////////////////////////////////////////////////
                        $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_from_adress') ) ; //'"'. 'Booking sender' . '" <' . $booking_form_show['email'].'>';
                        $mail_recipient =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );//'"Booking receipent" <' .get_option('admin_email').'>';
                        $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_subject') );
                        $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_content') );
                        $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
                        $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

                        $mail_body = str_replace('[bookingtype]', $bk_title, $mail_body);
                        if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( $my_dates4emeil );
                        else                                                  $my_dates_4_send = change_date_format($my_dates4emeil);
                        $mail_body = str_replace('[dates]',  $my_dates_4_send , $mail_body);
                        $mail_body = str_replace('[check_in_date]',$my_check_in_date , $mail_body);
                        $mail_body = str_replace('[check_out_date]',$my_check_out_date , $mail_body);
                        $mail_body = str_replace('[id]',$booking_id , $mail_body);

                        $mail_body = str_replace('[content]', $booking_form_show['content'], $mail_body);
                        $mail_body = str_replace('[name]', $booking_form_show['name'], $mail_body);
                        $mail_body = str_replace('[cost]', $my_cost, $mail_body);
                        $mail_body = str_replace('[siteurl]', htmlspecialchars_decode( '<a href="'.site_url().'">' . site_url() . '</a>'), $mail_body);
                        $mail_body = str_replace('[moderatelink]', htmlspecialchars_decode(
                                '<a href="'.site_url()  . '/wp-admin/admin.php?page='. WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME .'wpdev-booking&booking_type='.$bktype.'&moderate_id='. $booking_id .'">'
                                . __('here','wpdev-booking') . '</a>'), $mail_body);


                        $mail_body = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body, $booking_id );



                        if ( isset($booking_form_show['secondname']) ) $mail_body = str_replace('[secondname]', $booking_form_show['secondname'], $mail_body);

                        $mail_subject = str_replace('[name]', $booking_form_show['name'], $mail_subject);
                        if ( isset($booking_form_show['secondname']) ) $mail_subject = str_replace('[secondname]', $booking_form_show['secondname'], $mail_subject);

                        $mail_headers = "From: $mail_sender\n";
                        $mail_headers .= "Content-Type: text/html\n";

                        if ( strpos($mail_recipient,'[visitoremeil]') !== false ) {
                            $mail_recipient = str_replace('[visitoremeil]',$booking_form_show['email'],$mail_recipient);
                        }

                        if (get_bk_option( 'booking_is_email_reservation_adress'  ) != 'Off')
                            if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body,'admin@blank.com') === false ) )
                                if ($is_send_emeils != 0 )
                                    @wp_mail($mail_recipient, $mail_subject, $mail_body, $mail_headers);
                        /////////////////////////////////////////////////////////////////////////

                        if (get_bk_option( 'booking_is_email_newbookingbyperson_adress'  ) == 'On') {

                            $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_newbookingbyperson_adress') ) ; //'"'. 'Booking sender' . '" <' . $booking_form_show['email'].'>';
                            $mail_recipient =  $booking_form_show['email'];
                            $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_newbookingbyperson_subject') );
                            $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_newbookingbyperson_content') );
                            $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
                            $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

                            
                            $mail_body = str_replace('[bookingtype]', $bk_title, $mail_body);
                            if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( $my_dates4emeil );
                            else                                                  $my_dates_4_send = change_date_format($my_dates4emeil);
                            $mail_body = str_replace('[dates]',  $my_dates_4_send , $mail_body);
                            $mail_body = str_replace('[check_in_date]',$my_check_in_date , $mail_body);
                            $mail_body = str_replace('[check_out_date]',$my_check_out_date , $mail_body);
                            $mail_body = str_replace('[id]',$booking_id , $mail_body);


                            $mail_body = str_replace('[content]', $booking_form_show['content'], $mail_body);
                            $mail_body = str_replace('[name]', $booking_form_show['name'], $mail_body);
                            $mail_body = str_replace('[cost]', $my_cost, $mail_body);
                            $mail_body = str_replace('[siteurl]', htmlspecialchars_decode( '<a href="'.site_url().'">' . site_url() . '</a>'), $mail_body);
                            $mail_body = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body, $booking_id );

                            if ( isset($booking_form_show['secondname']) ) $mail_body = str_replace('[secondname]', $booking_form_show['secondname'], $mail_body);

                            $mail_subject = str_replace('[name]', $booking_form_show['name'], $mail_subject);
                            if ( isset($booking_form_show['secondname']) ) $mail_subject = str_replace('[secondname]', $booking_form_show['secondname'], $mail_subject);

                            $mail_headers = "From: $mail_sender\n";
                            $mail_headers .= "Content-Type: text/html\n";

                            if ( strpos($mail_recipient,'[visitoremeil]') !== false ) {
                                $mail_recipient = str_replace('[visitoremeil]',$booking_form_show['email'],$mail_recipient);
                            }
                            if ( strpos($mail_recipient,'@blank.com') === false )
                                if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body,'admin@blank.com') === false ) )
                                    if ($is_send_emeils != 0 )
                                        @wp_mail($mail_recipient, $mail_subject, $mail_body, $mail_headers);

                        }
                    }
                do_action('wpdev_new_booking',$booking_id, $bktype, str_replace('|',',',$dates), array($start_time, $end_time ) ,$sdform );

                ?> <script type="text/javascript"> setReservedSelectedDates('<?php echo $bktype; ?>'); </script>  <?php

            }

            // ReUpdate booking resource TYPE if its needed here
            if (! empty($dates) ) // check to have dates not empty
                make_bk_action('wpdev_booking_reupdate_bk_type_to_childs', $booking_id, $bktype, str_replace('|',',',$dates),  array($start_time, $end_time ) , $sdform );

}
?>