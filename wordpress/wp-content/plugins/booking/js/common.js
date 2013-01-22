// Scroll to script
function makeScroll(object_name) {
        var targetOffset = jQuery( object_name ).offset().top;
        targetOffset = targetOffset - 50;
        if (targetOffset<0) targetOffset = 0;
        jQuery('html,body').animate({scrollTop: targetOffset}, 1000);
}

// Show Yes/No dialog
function are_you_sure( message_question ){
        var answer = confirm( message_question );
        if ( answer) { return true; }
        else         { return false;}
}

// Display error message directly under a form element
// element : form element in error
// errorMessage : message to display
function showErrorMessage( element , errorMessage) {

    jQuery("[name='"+ element.name +"']")
            .fadeOut( 350 ).fadeIn( 300 )
            .fadeOut( 350 ).fadeIn( 400 )
            .animate( {opacity: 1}, 4000 )
    ;  // mark red border
    jQuery("[name='"+ element.name +"']")
            .after('<div class="wpdev-help-message">'+ errorMessage +'</div>'); // Show message
    jQuery(".wpdev-help-message")
            .css( {'color' : 'red'} )
            .animate( {opacity: 1}, 10000 )
            .fadeOut( 2000 );   // hide message
    element.focus();    // make focus to elemnt
    return;
}

// Adds a new set of allocations to a booking
// submit_form : form being submitted
function add_booking_allocation(submit_form) {

    if(submit_form.firstname.value === '') {
        showErrorMessage( submit_form.firstname, 'This field is required' );
        return;
    }
    else if(submit_form.num_guests_m.value === '' && submit_form.num_guests_f.value === '' && submit_form.num_guests_x.value === '') {
        showErrorMessage( submit_form.num_guests, 'At least one guest must be specified' );
        return;
    }
    else if(typeof document.getElementById('calendar_booking1').value === "undefined" || 
            document.getElementById('calendar_booking1').value === '') {
        jQuery('#ajax_respond').html("Select a date or date-range for the booking.")
            .css( {'color' : 'red'} )
            .animate( {opacity: 1}, 10000 )
            .fadeOut( 2000 );   // hide message
        return;
    }

    // build our list of space-delimited resource properties
    var resource_properties = "";
    if (jQuery('#resource_property_selection').is(":visible")) {
        for (var i = 0; i < submit_form.resource_property.length; i++) {
            if (submit_form.resource_property[i].checked)
                resource_properties += submit_form.resource_property[i].value + ",";
        }

        // only validate if resource properties are visible
        if (resource_properties.length <= 0) {
            jQuery('#ajax_respond').html("At least one property must be selected")
            .css({ 'color': 'red' })
            .animate({ opacity: 1 }, 10000)
            .fadeOut(2000);   // hide message
            return;
        }
    }
        
    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'ADD_ALLOCATION',
            dates: document.getElementById('calendar_booking1').value,
            firstname: submit_form.firstname.value,
            num_guests_m : submit_form.num_guests_m.value,
            num_guests_f : submit_form.num_guests_f.value,
            num_guests_x : submit_form.num_guests_x.value,
            req_room_size : submit_form.req_room_size.value,
            req_room_type : submit_form.req_room_type.value,
            booking_resource : submit_form.booking_resource.value,
            resource_property : resource_properties
        }
    });
}

// this will enable editing of the fields on the given allocation row.
// rowid : row id in allocation table
function edit_allocation(rowid) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'EDIT_ALLOCATION',
            rowid : rowid
        }
    });
}

// this will save the fields on the given allocation row.
// rowid : row id in allocation table
function save_allocation(rowid) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_ALLOCATION',
            rowid : rowid,
            allocation_name : document.getElementById('allocation_name' + rowid).value,
            resource_id : document.getElementById('booking_resource' + rowid).value
        }
    });
}

// this will delete the given allocation row.
// rowid : row id in allocation table
function delete_allocation(rowid) {

    if (are_you_sure('Remove allocation ' + document.getElementById('allocation_name'+rowid).innerHTML + '?')) {
        jQuery.ajax({                                           // Start Ajax Sending
            url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
            data:{
                ajax_action : 'DELETE_ALLOCATION',
                rowid : rowid
            }
        });
    }
}

// this will toggle the status of a booking date from pending/available
// or pending/checked out/checked in/no show if on current day
// rowid : row id in allocation table
// booking_date : date in format dd.MM.yyyy
function toggle_booking_date(rowid, booking_date) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'TOGGLE_BOOKING_DATE',
            rowid : rowid,
            booking_date : booking_date
        }
    });
}

// this will toggle the gender of the given allocation
// rowid : row id in allocation table
function toggle_gender(rowid) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'TOGGLE_GENDER',
            rowid : rowid
        }
    });
}

// this will toggle the checkout status of a booking date
// rowid : row id in allocation table
// booking_date : date in format dd.MM.yyyy
function toggle_checkout_on_booking_date(rowid, booking_date) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'TOGGLE_CHECKOUT_ON_BOOKING_DATE',
            rowid : rowid,
            booking_date : booking_date
        }
    });
}
    
// this will toggle the checkout status of an allocation from the allocation view
// resource_id : id of resource allocation belongs to
// allocation_id : id of allocation
// posn : 0-based index of *date* where the checkout status was toggled.
//        this is required as it is possible to have multiple checkout dates for a single allocation.
function toggle_checkout_for_allocation(resource_id, allocation_id, posn) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'TOGGLE_CHECKOUT_FOR_ALLOCATION',
            resource_id : resource_id,
            allocation_id : allocation_id,
            posn : posn
        }
    });
}

// this will toggle the checkout status of all applicable allocations for the given booking id
// booking_id : id of booking
function toggle_checkout_for_booking(booking_id) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'TOGGLE_CHECKOUT_FOR_BOOKING',
            booking_id : booking_id
        }
    });
}

/**
    * This will shift the dates on the calendar.
    * direction : one of 'left' or 'right'
    */
function shift_availability_calendar(direction) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'PAGE_AVAILABILITY_TABLE_LEFT_RIGHT',
            direction : direction
        }
    });
}
    
// this will add a comment to the current booking.
function add_booking_comment(submit_form) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'ADD_BOOKING_COMMENT',
            booking_comment : submit_form.booking_comment.value
        }
    });
}

// Check fields at form and then send request
function save_booking(submit_form){

    // Simple form validation here
    for (i=0; i<submit_form.elements.length; i++) {
        var element = submit_form.elements[i];

        if ( (element.type !=='button') && (element.type !=='hidden') && ( element.name !== ('calendar_booking1') )   ) {           // Skip buttons and hidden element - type
            // Validation Check --- Requred fields
            if ( element.className.indexOf('wpdev-validates-as-required') !== -1 ){
                if  ( element.value === '')   {
                    showErrorMessage( element , 'This field is required'); 
                    return;
                }
            }

            // check currency correctly formatted
            if ( element.className.indexOf('wpdev-validates-as-currency') !== -1 ){
                var is_numeric = /^\d*([\.]{0,1})\d*$/;  // only one separate between digits
                if  ( ! is_numeric.test(element.value) )   {
                    showErrorMessage( element , 'Only numeric values allowed. e.g. 9.90'); 
                    return;
                }
            }
        }

    }  // End Fields Loop

    jQuery('#submitting').html('<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>');
    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_BOOKING',
            firstname: submit_form.firstname.value,
            lastname: submit_form.lastname.value,
            referrer: submit_form.referrer.value,
            deposit_paid: submit_form.deposit_paid.value,
            amount_to_pay: submit_form.amount_to_pay.value
        }
    });
}
    
// this will enable editing of the fields on the given resource id.
// resource_id : resource id to edit
function edit_resource(resource_id) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'EDIT_RESOURCE',
            resource_id : resource_id
        }
    });
}

// this will delete by the given resource id.
// resource_id : resource id to delete
function delete_resource(resource_id) {

    if (are_you_sure('Are you sure you want to delete ' + document.getElementById('resource_name'+resource_id).innerHTML + '?')) {
        jQuery.ajax({                                           // Start Ajax Sending
            url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
            data:{
                ajax_action : 'DELETE_RESOURCE',
                resource_id : resource_id
            }
        });
    }
}

// this will save the given resource by id.
// resource_id : resource id to save
function save_resource(resource_id) {

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SAVE_RESOURCE',
            resource_id : resource_id,
            resource_name : document.getElementById('resource_name'+resource_id).value
        }
    });
}

// Updates the daily summary data on the page for the given date.
// selected_date : date in format yy-mm-dd
function select_daily_summary_date(selected_date) {

    jQuery('#ajax_respond').html('<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>');
    jQuery('#daily_summary_contents').html('');  // blank out contents while loading

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'SELECT_DAILY_SUMMARY_DAY',
            calendar_selected_date : selected_date
        }
    });
}

// resets sample test data
function generate_test_data(submit_form){
    jQuery('#ajax_respond').html('<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>');

    jQuery.ajax({                                           // Start Ajax Sending
        url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Oops sorry.. we messed up somewhere...');}},
        data:{
            ajax_action : 'GENERATE_TEST_DATA'
        }
    });
}
    
