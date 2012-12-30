<?php

/**
 * Create random booking/allocation data.
 */
class GenerateTestData extends XslTransform {

    var $lastCommand;

    /** 
     * Default constructor.
     */
    function GenerateTestData() {
        $this->lastCommand = array();
    }

    /**
     * Creates a locked page with the given title and contents.
     * $name : name (slug) of new page
     * $title : title of page
     * $content : full contents of page
     * $parent_post_id : (optional)  parent page post id
     * Returns new post id
     */
    function createReadOnlyPage($name, $title, $content, $parent_post_id = 0) {
        $my_post = array(
          'post_title'    => $title,
          'post_content'  => $content,
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type'     => 'page',
          'post_name'     => $name,
          'post_parent'   => $parent_post_id,
          'comment_status' => 'closed',
          'ping_status'   => 'closed'
        );

        // Insert the page into the database
        $post_id = wp_insert_post( $my_post, true );

        if (is_wp_error($post_id)) {
            $this->lastCommand[] = $post_id->get_error_message();
            $post_id = 0;
        } else {
            $this->lastCommand[] = "inserted page with id: $post_id";
            update_post_meta($post_id, '_wp_page_template', 'sidebar-page.php'); // add sidebar to page
        } 
        error_log(end($this->lastCommand));
        return $post_id;
    }

    /**
     * DELETES and recreates all test data in the database.
     */
    function reloadTestData() {

        $help_id = $this->createReadOnlyPage('help', 'Help',           '<ul> 	         <li><a href="'.home_url().'/help/pages">Pages</a></li> 	         <li><a href="'.home_url().'/help/faq">FAQ</a></li>           </ul>');

        // on previous success, create help sub-pages
        if ($help_id > 0) {

            $pages_id = $this->createReadOnlyPage('pages', 'Pages',               '<ul> 	             <li><a href="'.home_url().'/help/pages/add-edit-booking">Add/Edit Booking</a></li> 	             <li><a href="'.home_url().'/help/pages/allocations">Allocations</a></li> 	             <li><a href="'.home_url().'/help/pages/bookings">Bookings</a></li> 	             <li><a href="'.home_url().'/help/pages/daily-summary">Daily Summary</a></li> 	             <li><a href="'.home_url().'/help/pages/housekeeping">Housekeeping</a></li> 	             <li><a href="'.home_url().'/help/pages/resources">Resources</a></li>               </ul>',
              $help_id);


            // create individual help pages
            if ($pages_id > 0) {
                $post_id = $this->createReadOnlyPage('add-edit-booking', 'Add/Edit Booking', '<h1>Introduction</h1> Add a new booking/allocation or edit an existing booking. <h1><a name="Details"></a>Details</h1> <img src="http://hostel-reservations.googlecode.com/svn/wiki/images/add_booking_sample.png" alt="" />  Click "New Booking" under the menu to enter a new booking. To edit an existing booking, click on the link/icon of the booking/allocation to edit in the Allocation or Booking view.  First Name, Last Name: self-explanatory. this is the name the booking will be under. Only a first name is required.  Booked by: How the booking was originally made. e.g. HostelWorld, HostelBookers, Telephone, Walk-in, etc..  Audit Log/Comments: User comments can be entered here. e.g. Amount payable on arrival can be manually entered here. Future functionality may include a separate field to sum payment amounts for each booking/day. This also holds a log of all changes made to the booking since it was created including changes to the allocations, change in status (from reserved to paid for example) along with the date/time and user who made the change. <h2><a name="Allocations"></a>Allocations</h2> This is where we define all the guest(s) for this booking and their room/bed allocations. Select the dates for the booking using the calendar widget.  Room/Bed: Select an individual bed (for 1 guest), or select a room/group to auto-allocate the number of guest among the available beds.  Requested Room Type: Select from Mixed, Male or Female rooms. Rooms that have defined room types will be assigned first followed by rooms without specific gender assignments (see Resources).  Assign To: Only assign to rooms with the selected properties ticked (see Resource Properties).  Clicking "Add" will allocate the guest(s) to the selected group/room(s) displayed in a table placed below under the status of "Reserved". Clicking on the date cell in the table will toggle the state from Reserved to Paid/Checked-in, Free, etc..  "Save" will permanently commit these changes.',
                  $pages_id
                );

                $post_id = $this->createReadOnlyPage('allocations', 'Allocations', '<h1>Introduction</h1> This view allows a user to see at a glance which rooms are full/empty on a particular day or set of days. <h1><a name="Details"></a>Details</h1> <img src="http://hostel-reservations.googlecode.com/svn/wiki/images/allocations_sample.png" alt="" />  Enter the from and to dates and click apply to perform the search. Dates are always inclusive. A pull-tab will show/hide the legend showing the colours corresponding to current state of the allocation for that date (e.g. paid, reserved, etc..)  Allocations will be displayed ordered by group and resource (order as shown on the Resources page).  Clicking on any of the allocations will bring up the Edit Booking form for that particular booking.  Print: renders a printer-friendly version of this page (not implemented).  Export: exports the current contents of the page to CSV/Excel (not implemented).',
                  $pages_id
                );

                $post_id = $this->createReadOnlyPage('bookings', 'Bookings', '<h1>Introduction</h1> The search page allows a user to fetch an existing booking. <h1><a name="Details"></a>Details</h1> <img src="http://hostel-reservations.googlecode.com/svn/wiki/images/bookings_sample.png" alt="" />  Booking Status: Only show those bookings with <em>any</em> allocation for a particular booking matching this status (e.g. reserved, paid, cancelled). Default is ALL.  Date from/to: provide the from/to dates inclusive to search by. The dates here is used in conjunction with the "Match Dates By" field following.  Match Dates By: <ul> 	<li>Check-In Date (default): Show those bookings where the "check-in" date for a booking (the first date of a date-range for an allocation) falls within the to/from dates provided.</li> 	<li>Check-Out Date: Show those bookings where the "check-out" date for a booking (the last date of a date-range for an allocation) falls within the to/from dates provided.</li> 	<li>Reservation Date (Any): Show those bookings where <em>any</em> allocation date (regardless of status) falls within the to/from dates provided.</li> 	<li>Date Added: Show those bookings where the date the booking was created in the system falls within the to/from dates provided.</li> </ul> Search by Booking ID: enter the unique booking id to search for an exact booking  Search by Name: Show those bookings where either the first/last name for the booking or the guest name for an allocation matches. Search is <em>NOT</em> case sensitive. (<tt>*</tt>) can be used as a wildcard for any number of characters.  Example 1: <tt>sara*</tt> will match "sara" or "sarah" or "sarandon".  Example 2: <tt>rebe*a</tt> will match "Rebecca" or "Rebeka".  Print: displays a printer-friendly table of the current results along with a print dialog (not yet implemented).  Export: exports the current results to a CSV/Excel file (not yet implemented). <h2><a name="Search_Results"></a>Search Results</h2> ID: ID of the booking as well as the creation date and user who created the booking.  Tags: Shows the properties for the booking. This includes: the room(s) allocated, the active statuses and the manner in which the booking was made (e.g. telephone, HostelWorld, etc...)  Booking Details: Names/Guests and user-created comments.  Booking Dates: Dates across all allocations.  Actions: Clicking on the pencil icon will redirect to the Edit Booking screen. Clicking on the Checkout icon will change all allocations ending today to "checked-out" (not yet implemented).',                  $pages_id
                );
            }
        }
    }

    function getScriptOutput() {
        return implode(',', $this->lastCommand);
    }

    /**
     * Fetches this page in the following format:
     * <view>
     * </view>
     */
    function toXml() {
        $domtree = new DOMDocument('1.0', 'UTF-8');
        $xmlRoot = $domtree->appendChild($domtree->createElement('view'));
        $xmlRoot->appendChild($domtree->createElement('lastCommand', $this->getScriptOutput()));
        return $domtree->saveXML();
    }

    /**
     * Returns the filename for the stylesheet to use during transform.
     */
    function getXslFilename() {
        return WPDEV_BK_PLUGIN_DIR. '/include/generate_test_data.xsl';
    }
}

?>
