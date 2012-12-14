<?php

/**
 * Wordpress Plugin bootstrap. 
 * From here, all other classes are defined and bind points made to wordpress.
 */
class WP_HostelBackoffice {

    /**
     * Default constructor.
     * We will essentially need to call this on each request.
     */
    function __construct() {
        // Install / Uninstall
        register_activation_hook( WPDEV_BK_FILE, array(&$this,'activate'));
        register_deactivation_hook( WPDEV_BK_FILE, array(&$this,'deactivate'));

        // Create admin menu
        add_action('admin_menu', array(&$this, 'create_admin_menu'));
        add_action('admin_head', array(&$this, 'enqueue_scripts'));

        // top level admin menu option
        add_action('admin_bar_menu', array(&$this, 'add_admin_bar_bookings_menu'), 70);

        // On client-side menu
        add_action('wp_head', array(&$this, 'enqueue_scripts'));
        add_action('wp_head', array(&$this, 'print_js_css' ));

        // Template fallback: this gets called when not on admin page
        // TODO: can we create a template file the user references when creating a new page?
        add_action("template_redirect", array(&$this, 'my_template_redirect'));
    }

    /**
     * Called once on install.
     */
    function activate() {
        add_option('hbo_date_format' , get_option('date_format'));
        add_option('hbo_bookings_url', 'admin/bookings');
        add_option('hbo_allocations_url', 'admin/allocations');
        add_option('hbo_summary_url', 'admin/summary');
        add_option('hbo_resources_url', 'admin/resources');
        add_option('hbo_editbooking_url', 'edit-booking');
        $this->build_db_tables();
    }

    /**
     * Called once on uninstall.
     */
    function deactivate() {
        delete_option('hbo_date_format');
        delete_option('hbo_bookings_url');
        delete_option('hbo_allocations_url');
        delete_option('hbo_summary_url');
        delete_option('hbo_resources_url');
        delete_option('hbo_editbooking_url');
    }

    /**
     * Create an additional admin menu for this plugin.
     */    
    function create_admin_menu() {
        $title = __('Bookings', 'wpdev-booking');
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // M A I N     B O O K I N G
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook1 = add_menu_page( __('Bookings', 'wpdev-booking'),  $title, 'administrator',
                WPDEV_BK_FILE . 'wpdev-booking', array(&$this, 'content_of_bookings_page'),  WPDEV_BK_PLUGIN_URL . '/img/calendar-16x16.png'  );
        add_action("admin_print_scripts-" . $pagehook1 , array( &$this, 'add_js_css_files'));
            
        ///////////////// ALLOCATIONS VIEW /////////////////////////////////////////////
        $pagehook6 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Allocations', 'wpdev-booking'), __('Allocations', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-allocations', array(&$this, 'content_of_allocations_page')  );
        add_action("admin_print_scripts-" . $pagehook6 , array( &$this, 'add_js_css_files'));
            
        ///////////////// DAILY SUMMARY /////////////////////////////////////////////
        $pagehook5 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Summary', 'wpdev-booking'), __('Summary', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-summary', array(&$this, 'content_of_summary_page')  );
        add_action("admin_print_scripts-" . $pagehook5 , array( &$this, 'add_js_css_files'));
            
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S E R V A T I O N
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook2 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Add Booking', 'wpdev-booking'), __('Add Booking', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-reservation', array(&$this, 'content_of_edit_booking_page')  );
        add_action("admin_print_scripts-" . $pagehook2 , array( &$this, 'add_js_css_files'));
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S O U R C E S     Management
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook4 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Resources', 'wpdev-booking'), __('Resources', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-resources', array(&$this, 'content_of_resources_page')  );
        add_action("admin_print_scripts-" . $pagehook4 , array( &$this, 'add_js_css_files'));

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // S E T T I N G S
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook3 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Booking settings customizations', 'wpdev-booking'), __('Settings', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-option', array(&$this, 'content_of_settings_page')  );
        add_action("admin_print_scripts-" . $pagehook3 , array( &$this, 'add_js_css_files'));
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    }

    /**
     * Safely enqueues any scripts/css to be run.
     */
    function enqueue_scripts() {
error_log('enqueue scripts: jquery');
        wp_enqueue_script('jquery');
        if (strpos($_SERVER['REQUEST_URI'], 'wpdev-booking.phpwpdev-booking') !== false) {
            if (defined('WP_ADMIN') && WP_ADMIN === true) { 
error_log("is this used? jquery-ui-dialog");
                wp_enqueue_script('jquery-ui-dialog'); 
            }
            wp_enqueue_style('hbo-jquery-ui', WPDEV_BK_PLUGIN_URL. '/css/jquery-ui.css', array(), false, 'screen');
        }
    }

    /**
     * Add hook for printing scripts only when displaying pages for this plugin.
     */
    function add_js_css_files() {
        // Write inline scripts and CSS at HEAD
        add_action('admin_head', array(&$this, 'print_js_css' ));
    }

    /**
     * Print     J a v a S c r i p t   &    C S S    scripts for admin and client side.
     */
    function print_js_css() {

error_log('print scripts: jquery');
        wp_print_scripts('jquery');
        //wp_print_scripts('jquery-ui-core');

        $hh = new HtmlHeaders();
        echo $hh->toHtml();
    }

    /**
     * Write contents of Bookings View
     */
    function content_of_bookings_page() {

        // if filter_status is defined, we are on the bookings view
        if (isset($_POST['filter_status']) && trim($_POST['filter_status']) != '') {
            $bv = new BookingView(
                DateTime::createFromFormat('!Y-m-d', $_POST['bookingmindate'], new DateTimeZone('UTC')),
                DateTime::createFromFormat('!Y-m-d', $_POST['bookingmaxdate'], new DateTimeZone('UTC')));
            $bv->status = $_POST['filter_status'];
            $bv->matchName = trim($_POST['filter_name']) == '' ? null : $_POST['filter_name'];
            $bv->dateMatchType = $_POST['filter_datetype'];
//            $bv->resourceId = $_POST['filter_resource_id'];
            $bv->doSearch();
        } 
        // redo search if we've already done one
        else if (isset($_SESSION['BOOKING_VIEW'])) {
            $bv = $_SESSION['BOOKING_VIEW'];
            $bv = new BookingView($bv->minDate, $bv->maxDate, 
                $bv->dateMatchType, $bv->status, $bv->resourceId, $bv->matchName);
            $bv->doSearch();
        } 
        // leave as blank view if it is the first time
        else {
            $bv = new BookingView();
        }
        $_SESSION['BOOKING_VIEW'] = $bv;

error_log($bv->toXml());
        echo $bv->toHtml();
    }

    /**
     * Write contents of Allocations View.
     */
    function content_of_allocations_page() {

        // if allocation date is defined, we are on the allocations view
        if (isset($_POST['allocationmindate']) && trim($_POST['allocationmindate']) != '') {
            $av = new AllocationView(
                DateTime::createFromFormat('!Y-m-d', $_POST['allocationmindate'], new DateTimeZone('UTC')),
                DateTime::createFromFormat('!Y-m-d', $_POST['allocationmaxdate'], new DateTimeZone('UTC')));
        }
        // redo search on allocation dates
        else if (isset($_SESSION['ALLOCATION_VIEW'])) {
            $av = new AllocationView($_SESSION['ALLOCATION_VIEW']->showMinDate, $_SESSION['ALLOCATION_VIEW']->showMaxDate);
        }
        // use default dates if it's the first time 
        else {
            $av = new AllocationView();
        }

        $av->doSearch();
        $_SESSION['ALLOCATION_VIEW'] = $av;
        
error_log($av->toXml());
        echo $av->toHtml();
    }

    /**
     * Write contents of Add/Edit Booking page
     */
    function content_of_edit_booking_page() {
        // always start with a new object
        $_SESSION['ADD_BOOKING_CONTROLLER'] = new AddBooking();

        // if we are editing an existing booking...
        if(isset($_REQUEST['bookingid'])) {
            // TODO: rename "ADD_BOOKING_CONTROLLER" to "EDIT_BOOKING_CONTROLLER", AddBooking to EditBooking
            $_SESSION['ADD_BOOKING_CONTROLLER']->load($_REQUEST['bookingid']);
        } 
            
        echo $_SESSION['ADD_BOOKING_CONTROLLER']->toHtml();
    }

    /**
     * Write the contents of the Daily Summary page.
     */
    function content_of_summary_page() {
        $ds = new DailySummary();
        $ds->doSummaryUpdate();
        echo $ds->toHtml();
    }

    /**
     * Write the contents of the booking resources page.
     */
    function content_of_resources_page(){

        if (isset($_GET['editResourceId'])) {

            $rpp = new ResourcePropertyPage($_GET['editResourceId']);

            // TODO: move to ResourcePropertyPage
            // SAVE button was pressed on the edit resource property page
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $propertyIds = isset($_POST['resource_property']) ? $_POST['resource_property'] : array();
                ResourceDBO::updateResourceProperties($_GET['editResourceId'], $propertyIds);
                $rpp->isSaved = true;
            }
                
error_log($rpp->toXml());
            echo $rpp->toHtml();

        } else {
            $resources = new Resources();
    
            try {
                // TODO: move to Resources page
                // if the user has just submitted an "Add new resource" request
                if ( isset($_POST['resource_name_new']) && $_POST['resource_name_new'] != '') {
                    ResourceDBO::insertResource($_POST['resource_name_new'], $_POST['resource_capacity_new'], 
                        $_POST['resource_parent_new'] == 0 ? null : $_POST['resource_parent_new'],
                        $_POST['resource_type_new']);
                }
        
            } catch (DatabaseException $de) {
                $resources->errorMessage = $de->getMessage();
            }
            echo $resources->toHtml();
        }
    }

    /**
     * Write the contents of the Settings page.
     */
    function content_of_settings_page() {
        $s = new Settings();
        if (false === empty($_POST)) {
            $s->updateOptions($_POST);
        }
error_log($s->toXml());
error_log(var_export($_POST, TRUE));
        echo $s->toHtml();
    }

    /**
     * Display a top-level menu dropdown on the admin menu (when logged in as admin).
     */
    function add_admin_bar_bookings_menu(){
        global $wp_admin_bar;
        
        $title = __('Bookings', 'wpdev-booking');
        $update_title = $title;

        $link_bookings = admin_url('admin.php'). "?page=" . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . "wpdev-booking";
        $link_settings = admin_url('admin.php'). "?page=" . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . "wpdev-booking-option";

        $wp_admin_bar->add_menu(
                array(
                    'id' => 'booking_options',
                    'title' => $update_title ,
                    'href' => $link_bookings
                    )
                );

        $wp_admin_bar->add_menu(
                array(
                    'parent' => 'booking_options',
                    'title' => __( 'Settings', 'wpdev-booking' ),
                    'href' => $link_settings,
                    'id' => 'booking_settings'
                    )
                );
    }

    /**
     * This will override the template for the pages associated with this plugin
     * based on the name of the page.
     * These can be set under the Settings for the plugin.
     */
    function my_template_redirect() {
        $this->do_redirect_for_page(get_option('hbo_allocations_url'), 'allocations.php');
        $this->do_redirect_for_page(get_option('hbo_bookings_url'), 'bookings.php');
        $this->do_redirect_for_page(get_option('hbo_summary_url'), 'summary.php');
        $this->do_redirect_for_page(get_option('hbo_editbooking_url'), 'edit-booking.php');
        $this->do_redirect_for_page(get_option('hbo_resources_url'), 'resources.php');
    }

    /**
     * Redirects to page if the current pagename matches $url.
     * $url : url to redirect if matched
     * $templatefile : name of template file to redirect to
     */
    function do_redirect_for_page($url, $templatefile) {
        global $wp;
        $plugindir = dirname( __FILE__ ) . '/..';

        if (isset($wp->query_vars["pagename"]) && $wp->query_vars["pagename"] == $url) {
            if (file_exists(TEMPLATEPATH . '/' . $templatefile)) {
                $return_template = TEMPLATEPATH . '/' . $templatefile;
            } else {
                $return_template = $plugindir . '/templates/' . $templatefile;
            }
            $this->do_redirect($return_template);
        }
    }

    /**
     * Includes the php file specified and terminates.
     * $url : location of php file to include.
     */
    function do_redirect($url) {
        global $post, $wp_query;
        if (have_posts()) {
            include($url);
            die();
        } else {
            $wp_query->is_404 = true;
        }
    }

    /**
     * Create/update all tables required for this booking.
     */
    function build_db_tables() {
        global $wpdb;
        $charset_collate = '';
        if (false === empty($wpdb->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        }

        if ( false == $this->does_table_exist('booking') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."booking (
                        booking_id bigint(20) unsigned NOT NULL auto_increment,
                        firstname varchar(50) NOT NULL,
                        lastname varchar(50),
                        referrer varchar(50),
                        deposit_paid decimal(10,2),
                        amount_to_pay decimal(10,2),
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (booking_id)
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }

        if ( false == $this->does_table_exist('bookingresources') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."bookingresources (
                        resource_id bigint(20) unsigned NOT NULL auto_increment,
                        name varchar(50) NOT NULL,
                        parent_resource_id bigint(20) unsigned,
                        resource_type varchar(10) NOT NULL,
                        room_type varchar(2),
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (resource_id),
                        FOREIGN KEY (parent_resource_id) REFERENCES ".$wpdb->prefix ."bookingresources(resource_id)
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }

        if ( false == $this->does_table_exist('mv_resources_by_path') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."mv_resources_by_path (
                        resource_id bigint(20) unsigned NOT NULL,
                        name varchar(50) NOT NULL,
                        parent_resource_id bigint(20) unsigned,
                        path varchar(100) NOT NULL,
                        resource_type varchar(10) NOT NULL,
                        room_type varchar(2),
                        level int(10) unsigned NOT NULL,
                        number_children int(10) unsigned NOT NULL,
                        capacity int(10) unsigned NOT NULL,
                        PRIMARY KEY (resource_id)
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }
            
        if ( false == $this->does_table_exist('resource_properties') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."resource_properties (
                        property_id bigint(20) unsigned NOT NULL,
                        description varchar(20) NOT NULL,
                        PRIMARY KEY (property_id)
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
                
            $simple_sql = "INSERT INTO ".$wpdb->prefix ."resource_properties (property_id, description)
                            VALUES(%d, %s)";
            $wpdb->query($wpdb->prepare($simple_sql, 1, '4-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 2, '8-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 3, '10-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 4, '12-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 5, '14-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 6, '16-Bed Dorm'));
            $wpdb->query($wpdb->prepare($simple_sql, 7, 'Twin Room'));
            $wpdb->query($wpdb->prepare($simple_sql, 8, 'Double Room'));
            $wpdb->query($wpdb->prepare($simple_sql, 9, 'Double Deluxe Room'));
        }
            
        if ( false == $this->does_table_exist('resource_properties_map') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."resource_properties_map (
                        resource_id bigint(20) unsigned NOT NULL,
                        property_id bigint(20) unsigned NOT NULL,
                        FOREIGN KEY (resource_id) REFERENCES ".$wpdb->prefix ."bookingresources(resource_id),
                        FOREIGN KEY (property_id) REFERENCES ".$wpdb->prefix ."resource_properties(property_id)
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }
            
        if ( false == $this->does_table_exist('bookingcomment') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."bookingcomment (
                        comment_id bigint(20) unsigned NOT NULL auto_increment,
                        booking_id bigint(20) unsigned NOT NULL,
                        comment TEXT NOT NULL,
                        comment_type varchar(10) NOT NULL,
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        PRIMARY KEY (comment_id),
                        FOREIGN KEY (booking_id) REFERENCES ".$wpdb->prefix ."booking(booking_id)
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }
            
        if ( false == $this->does_table_exist('allocation') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."allocation (
                        allocation_id bigint(20) unsigned NOT NULL auto_increment,
                        booking_id bigint(20) unsigned NOT NULL,
                        resource_id bigint(20) unsigned NOT NULL,
                        guest_name varchar(50) NOT NULL,
                        gender varchar(1) NOT NULL,
                        req_room_size varchar(3),
                        req_room_type varchar(1),
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (allocation_id),
                        FOREIGN KEY (booking_id) REFERENCES ".$wpdb->prefix ."booking(booking_id),
                        FOREIGN KEY (resource_id) REFERENCES ".$wpdb->prefix ."bookingresources(resource_id) 
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }

        if ( false == $this->does_table_exist('bookingdates') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."bookingdates (
                        allocation_id bigint(20) unsigned NOT NULL,
                        booking_date date NOT NULL,
                        status varchar(10) NOT NULL,
                        checked_out varchar(1) NULL,
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        FOREIGN KEY (allocation_id) REFERENCES ".$wpdb->prefix ."allocation(allocation_id)
                    ) $charset_collate;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }
            
        if( false == $this->does_routine_exist('walk_tree_path')) {
            $simple_sql = "CREATE FUNCTION walk_tree_path(p_resource_id BIGINT(20) UNSIGNED) RETURNS VARCHAR(255)
                -- walks the bookingresources table from the given resource_id down to the root
                -- returns the path walked delimited with /
                BEGIN
                    DECLARE last_id BIGINT(20) UNSIGNED;
                    DECLARE parent_id BIGINT(20) UNSIGNED;
                    DECLARE return_val VARCHAR(255);
                        
                    SET return_val = p_resource_id;
                    SET parent_id = p_resource_id;
                    
                    WHILE parent_id IS NOT NULL DO
                        
                        SELECT parent_resource_id INTO parent_id
                        FROM ".$wpdb->prefix ."bookingresources
                        WHERE resource_id = parent_id;
                    
                        SET return_val = CONCAT(IFNULL(parent_id, ''), '/', return_val);
                    
                    END WHILE;
                        
                    RETURN return_val;
                    
                END;";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }
            
        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_resources_sub1 AS
                SELECT resource_id, name, parent_resource_id, walk_tree_path(resource_id) AS path, resource_type, room_type
                FROM ".$wpdb->prefix."bookingresources";

        if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
        }

        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_resources_by_path AS
                SELECT resource_id, name, parent_resource_id, path, resource_type, room_type,
                        LENGTH(path) - LENGTH(REPLACE(path, '/', '')) AS level,
                        (SELECT COUNT(*) FROM ".$wpdb->prefix."v_resources_sub1 s1 WHERE s1.path LIKE CAST(CONCAT(s.path, '/%%') AS CHAR) AND resource_type = 'bed') AS number_children,
                        (SELECT COUNT(*) FROM ".$wpdb->prefix."v_resources_sub1 s1 WHERE (s1.path LIKE CAST(CONCAT(s.path, '/%%') AS CHAR) OR s1.path = s.path) AND resource_type = 'bed') AS capacity
                    FROM ".$wpdb->prefix."v_resources_sub1 s
                    ORDER BY path";
// FIXME: remove number_children, rename capacity ==> number_beds

        if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
        }

        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_booked_capacity (booking_date, resource_id, used_capacity) AS
                SELECT bd.booking_date, alloc.resource_id, COUNT(*) used_capacity
                    FROM ".$wpdb->prefix."bookingdates bd
                    JOIN ".$wpdb->prefix."allocation alloc ON bd.allocation_id = alloc.allocation_id
                    GROUP BY bd.booking_date, alloc.resource_id";

        if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
        }

        $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_resource_availability (booking_date, resource_id, resource_name, path, capacity, used_capacity, avail_capacity) AS
                SELECT bc.booking_date, 
                        rp.resource_id, 
                        rp.name AS resource_name,
                        rp.path, 
                        rp.capacity, 
                        bc.used_capacity,
                        CAST(rp.capacity - IFNULL(bc.used_capacity, 0) AS SIGNED) AS avail_capacity 
                    FROM ".$wpdb->prefix."mv_resources_by_path rp
                    LEFT OUTER JOIN ".$wpdb->prefix."v_booked_capacity bc ON rp.resource_id = bc.resource_id
                    WHERE rp.number_children = 0
                    ORDER BY bc.booking_date, rp.path";

        if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
            error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
        }

        if ( false == $this->does_trigger_exist('trg_enforce_availability') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_enforce_availability
                -- this will raise an error by selecting from a non-existent table
                -- in order to enforce availability for a particular resource/date
                BEFORE INSERT ON ".$wpdb->prefix."bookingdates FOR EACH ROW
                BEGIN
                    DECLARE p_avail_capacity INT;
                    SELECT avail_capacity INTO p_avail_capacity
                    FROM ".$wpdb->prefix."v_resource_availability ra
                    JOIN ".$wpdb->prefix."allocation alloc ON ra.resource_id = alloc.resource_id
                    WHERE ra.booking_date = NEW.booking_date
                    AND alloc.allocation_id = NEW.allocation_id;
                        
                    IF p_avail_capacity <= 0 THEN
                        SELECT 'Reservation conflicts with an existing reservation' INTO p_avail_capacity 
                        FROM SANITY_CHECK_RESERVATION_CONFLICT_FOUND
                        WHERE SANITY_CHECK_RESERVATION_CONFLICT_FOUND.id = NEW.allocation_id;
                    END IF;
                END";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }

        if ( false == $this->does_trigger_exist('trg_mv_resources_by_path_ins') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_ins
                -- this will update the materialized view
                -- whenever an insert is made on the underlying table
                AFTER INSERT ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    INSERT INTO ".$wpdb->prefix."mv_resources_by_path
                    SELECT * FROM ".$wpdb->prefix."v_resources_by_path
                        WHERE resource_id = NEW.resource_id;

                    -- update the child count (could be clever by splitting path)
                    UPDATE ".$wpdb->prefix."mv_resources_by_path m
                      JOIN ".$wpdb->prefix."v_resources_by_path v
                        ON m.resource_id = v.resource_id
                       SET m.number_children = v.number_children,
                           m.capacity = v.capacity;
                END";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }

        if ( false == $this->does_trigger_exist('trg_mv_resources_by_path_upd') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_upd
                -- this will update the materialized view
                -- whenever an update is made on the underlying table
                AFTER UPDATE ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    -- currently only updatable field is name
                    IF NEW.name <> OLD.name AND NEW.resource_id = OLD.resource_id THEN
                        UPDATE ".$wpdb->prefix."mv_resources_by_path m
                          JOIN ".$wpdb->prefix."bookingresources br
                            ON m.resource_id = br.resource_id
                           SET m.name = br.name
                         WHERE m.resource_id = NEW.resource_id;
                    END IF;

                    /** DISABLED: no way to update any fields in wp_bookingresources except name
                    DELETE FROM ".$wpdb->prefix."mv_resources_by_path
                     WHERE resource_id = OLD.resource_id;

                    INSERT INTO ".$wpdb->prefix."mv_resources_by_path
                    SELECT * FROM ".$wpdb->prefix."v_resources_by_path
                     WHERE resource_id = NEW.resource_id;

                    -- update the child count (could be clever by splitting path)
                    UPDATE ".$wpdb->prefix."mv_resources_by_path m
                      JOIN ".$wpdb->prefix."v_resources_by_path v
                        ON m.resource_id = v.resource_id
                       SET m.number_children = v.number_children,
                           m.capacity = v.capacity;
                     */
                END";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }

        if ( false == $this->does_trigger_exist('trg_mv_resources_by_path_del') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_del
                -- this will update the materialized view
                -- whenever a delete is made on the underlying table
                AFTER DELETE ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    DELETE FROM ".$wpdb->prefix."mv_resources_by_path
                     WHERE resource_id = OLD.resource_id;

                    -- update the child count (could be clever by splitting path)
                    UPDATE ".$wpdb->prefix."mv_resources_by_path m
                      JOIN ".$wpdb->prefix."v_resources_by_path v
                        ON m.resource_id = v.resource_id
                       SET m.number_children = v.number_children,
                           m.capacity = v.capacity;
                END";

            if (false === $wpdb->query($wpdb->prepare($simple_sql))) {
                error_log($wpdb->last_error." executing sql: ".$wpdb->last_query);
            }
        }
    }

    /**
     * Check if table exists.
     * $tablename : name of table to check (with or without wp prefix)
     * Returns true or false.
     */
    function does_table_exist( $tablename ) {
        global $wpdb;
        if (strpos($tablename, $wpdb->prefix) === false) {
            $tablename = $wpdb->prefix . $tablename;   
        }

        $res = $wpdb->get_results($wpdb->prepare(
                "SELECT COUNT(*) AS count
                   FROM information_schema.tables
                  WHERE table_schema = '". DB_NAME ."'
                    AND table_name = %s", $tablename));
        return $res[0]->count > 0;
    }

    /**
     * Check if trigger exists.
     * $triggerName : name of trigger to check (with or without wp prefix)
     * Returns true or false.
     */
    function does_trigger_exist( $triggerName ) {
        global $wpdb;
        if (strpos($triggerName, $wpdb->prefix) === false) {
            $triggerName = $wpdb->prefix . $triggerName;   
        }

        $res = $wpdb->get_results($wpdb->prepare(
                "SELECT COUNT(*) AS count
                   FROM information_schema.triggers
                  WHERE trigger_schema = '". DB_NAME ."'
                    AND trigger_name = %s", $triggerName));
        return $res[0]->count > 0;
    }

    /**
     * Check if procedure/function exists.
     * $routineName : name of routine to check (with or without wp prefix)
     * Returns true or false.
     */
    function does_routine_exist( $routineName ) {
        global $wpdb;
        if (strpos($routineName, $wpdb->prefix) === false) {
            $routineName = $wpdb->prefix . $routineName;   
        }

        $res = $wpdb->get_results($wpdb->prepare(
                "SELECT COUNT(*) AS count
                   FROM information_schema.routines
                  WHERE routine_schema = '". DB_NAME ."'
                    AND routine_name = %s", $routineName));
        return $res[0]->count > 0;
    }
}

?>
