// Scroll to script
function makeScroll(object_name) {
        var targetOffset = jQuery( object_name ).offset().top;
        targetOffset = targetOffset - 50;
        if (targetOffset<0) targetOffset = 0;
        jQuery('html,body').animate({scrollTop: targetOffset}, 1000)
         
}

// Adds a new set of allocations to a booking
// submit_form : form being submitted
function add_booking_allocation(submit_form) {

    if(submit_form.firstname.value === '') {
        showErrorMessage( submit_form.firstname, message_verif_requred );
        return;
    }
    else if(submit_form.booking_resource.value === '0') {
        showErrorMessage( submit_form.booking_resource, message_verif_requred );
        return;
    }
    else if(typeof document.getElementById('calendar_booking1').value === "undefined" || 
            document.getElementById('calendar_booking1').value === '') {
        jQuery('#ajax_respond').html("Select a date or date-range for the booking.")
            .css( {'color' : 'red'} );
        return;
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
            num_visitors : submit_form.num_visitors.value,
            gender : submit_form.gender.value,
            booking_resource : submit_form.booking_resource.value
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

    if (bk_are_you_sure('Remove allocation ' + document.getElementById('allocation_name'+rowid).innerHTML + '?')) {
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
    for (i=0; i<submit_form.elements.length; i++)   {
        var element = submit_form.elements[i];
            
        if ( (element.type !=='button') && (element.type !=='hidden') && ( element.name !== ('calendar_booking1') )   ) {           // Skip buttons and hidden element - type

            // Validation Check --- Requred fields
            if ( element.className.indexOf('wpdev-validates-as-required') !== -1 ){             
                if  ( element.value === '')   {showErrorMessage( element , message_verif_requred);return;}
            }

        }

    }  // End Fields Loop

    //Show message if no selected days
    // TODO: server side check
//        if (document.getElementById('date_booking1').value == '')  {
//            alert(message_verif_selectdts);
//        }

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
            referrer: submit_form.referrer.value
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

    if (bk_are_you_sure('Are you sure you want to delete ' + document.getElementById('resource_name'+resource_id).innerHTML + '?')) {
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

