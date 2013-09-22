<?php
/*
Plugin Name: Hostel Backoffice
Plugin URI: http://demo.hostelbackoffice.net/
Description: Backoffice reservations and administration services for hostels / B & Bs.
Version: 0.1
Author: sir_studly
Author URI: http://www.hostelbackoffice.net
Tested WordPress Versions: 3.4
*/

/*  Copyright 2009 - 2012  http://www.hostelbackoffice.net  (email: info@hostelbackoffice.net),

    This file is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/


/*
-------------------------------------------------------
A c t u a l   T O D O   List:
-------------------------------------------------------
 ******************************************************
 * Checkout(s) on daily summary.
 * Allocations using resource properties
 * Add deposit paid/amount to pay into booking form
 * Add gender toggle into allocation table
 * Resources/rooms that can change between M/F/Mx (can we do this manually for now? how much work?)
 * Housekeeping page.
 * Add Allocations as a modal dialog.
 * How to handle canceled bookings? .. mark as canceled on booking? or on each date? what needs to be changed?
 * Rename Add Booking to Edit Booking.
 * Show user comments only checkbox on Add/Edit Bookings page.
 * Create script to generate dummy data.
 * Test scripts in selenium?
 * Remove all references of wpdev/booking.
 * Add translations __(...) for everything not in xsl.
 *
 */
// </editor-fold>


    // A J A X /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if ( isset( $_POST['ajax_action'] )) {
        require_once( dirname(__FILE__) . '/../../../wp-load.php' );
        @header('Content-Type: text/html; charset=' . get_option('blog_charset'));
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e     S T A T I C              //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (!defined('WP_BK_DEBUG_MODE'))    define('WP_BK_DEBUG_MODE',  false );
    if (!defined('WPDEV_BK_FILE'))       define('WPDEV_BK_FILE',  __FILE__ );

    if (!defined('WP_CONTENT_DIR'))      define('WP_CONTENT_DIR', ABSPATH . 'wp-content');                   // Z:\home\test.wpdevelop.com\www/wp-content
    if (!defined('WP_CONTENT_URL'))      define('WP_CONTENT_URL', site_url() . '/wp-content');    // http://test.wpdevelop.com/wp-content
    if (!defined('WP_PLUGIN_DIR'))       define('WP_PLUGIN_DIR', WP_CONTENT_DIR . '/plugins');               // Z:\home\test.wpdevelop.com\www/wp-content/plugins
    if (!defined('WP_PLUGIN_URL'))       define('WP_PLUGIN_URL', WP_CONTENT_URL . '/plugins');               // http://test.wpdevelop.com/wp-content/plugins
    if (!defined('WPDEV_BK_PLUGIN_FILENAME'))  define('WPDEV_BK_PLUGIN_FILENAME',  basename( __FILE__ ) );              // menu-compouser.php
    if (!defined('WPDEV_BK_PLUGIN_DIRNAME'))   define('WPDEV_BK_PLUGIN_DIRNAME',  plugin_basename(dirname(__FILE__)) ); // menu-compouser
    if (!defined('WPDEV_BK_PLUGIN_DIR')) define('WPDEV_BK_PLUGIN_DIR', WP_PLUGIN_DIR.'/'.WPDEV_BK_PLUGIN_DIRNAME ); // Z:\home\test.wpdevelop.com\www/wp-content/plugins/menu-compouser
    if (!defined('WPDEV_BK_PLUGIN_URL')) define('WPDEV_BK_PLUGIN_URL', WP_PLUGIN_URL.'/'.WPDEV_BK_PLUGIN_DIRNAME ); // http://test.wpdevelop.com/wp-content/plugins/menu-compouser


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   L O A D   F I L E S                      //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

//    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-functions.php')) {     // S u p p o r t    f u n c t i o n s
//        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-functions.php' ); }
//    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-widget.php')) {        // W i d g e t s
//        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-widget.php' ); }
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/common_functions.php')) {     // S u p p o r t    f u n c t i o n s
        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/common_functions.php' ); }

//    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/js/captcha/captcha.php'))  {             // C A P T C H A
//        require_once(WPDEV_BK_PLUGIN_DIR. '/js/captcha/captcha.php' );}

//    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/personal.php'))   {                  // O t h e r
//        require_once(WPDEV_BK_PLUGIN_DIR. '/inc/personal.php' ); }
//    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-lib.php')) {                // S u p p o r t    l i b
//        require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-bk-lib.php' ); }

//    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-class.php'))           // C L A S S    B o o k i n g
//        { require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wp_hostel_backoffice.php'))           // B O O T S T R A P
        { require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wp_hostel_backoffice.php' ); }

    //////////////////////// BEGIN CUSTOM CODE /////////////////////////////////////////////////////////////////////////////////////////////////////
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/xsl_transform.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/xsl_transform.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/booking_date.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/booking_date.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_row.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_row.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_table.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_table.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/resources_table.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/resources_table.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/resources.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/resources.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/resource_dbo.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/resource_dbo.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/resource_property_page.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/resource_property_page.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/add_booking.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/add_booking.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_dbo.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_dbo.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_strategy.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_strategy.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_exception.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_exception.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/database_exception.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/database_exception.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/db_transaction.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/db_transaction.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/booking_dbo.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/booking_dbo.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/booking_resource.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/booking_resource.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_view.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_view.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_view_resource.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_view_resource.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_cell.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_cell.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/booking_view.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/booking_view.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/booking_summary.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/booking_summary.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/booking_comment.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/booking_comment.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/booking_comment_log.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/booking_comment_log.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/daily_summary_resource.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/daily_summary_resource.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/daily_summary_data.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/daily_summary_data.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/daily_summary.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/daily_summary.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/settings.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/settings.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/html_headers.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/html_headers.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/generate_test_data.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/generate_test_data.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/generate_test_data_content.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/generate_test_data_content.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/run_unit_tests.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/run_unit_tests.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/run_unit_tests_content.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/run_unit_tests_content.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/allocation_availability.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/allocation_availability.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/help_page.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/help_page.class.php' ); }

    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/include/page_factory.class.php')) 
        { require_once(WPDEV_BK_PLUGIN_DIR. '/include/page_factory.class.php' ); }

    //////////////////////// END CUSTOM CODE /////////////////////////////////////////////////////////////////////////////////////////////////////
        
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // GET VERSION NUMBER
//    $plugin_data = get_file_data_wpdev(  __FILE__ , array( 'Name' => 'Plugin Name', 'PluginURI' => 'Plugin URI', 'Version' => 'Version', 'Description' => 'Description', 'Author' => 'Author', 'AuthorURI' => 'Author URI', 'TextDomain' => 'Text Domain', 'DomainPath' => 'Domain Path' ) , 'plugin' );
//    if (!defined('WPDEV_BK_VERSION'))    define('WPDEV_BK_VERSION',   $plugin_data['Version'] );                             // 0.1
            

    //    A J A X     R e s p o n d e r
//    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-ajax.php'))  
//        { require_once(WPDEV_BK_PLUGIN_DIR. '/lib/wpdev-booking-ajax.php' ); }
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/lib/ajax_controller.php'))  
        { require_once(WPDEV_BK_PLUGIN_DIR. '/lib/ajax_controller.php' ); }

    // RUN //
    error_reporting(E_ALL);
    session_start();
    //$wpdev_bk = new wpdev_booking(); 
    $_SESSION['WP_HOSTELBACKOFFICE'] = new WP_HostelBackoffice();

?>