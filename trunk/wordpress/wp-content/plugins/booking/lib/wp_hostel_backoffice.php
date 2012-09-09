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
    }

    /**
     * Called once on install.
     */
    function activate() {
        add_option( 'hbo_date_format' , get_option('date_format'));
        $this->build_db_tables();
    }

    /**
     * Called once on uninstall.
     */
    function deactivate() {
        delete_option( 'hbo_date_format');
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
        add_action("admin_print_scripts-" . $pagehook1 , array( &$this, 'add_admin_js_css_files'));
            
        ///////////////// ALLOCATIONS VIEW /////////////////////////////////////////////
        $pagehook6 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Allocations', 'wpdev-booking'), __('Allocations', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-allocations', array(&$this, 'content_of_allocations_page')  );
        add_action("admin_print_scripts-" . $pagehook6 , array( &$this, 'add_admin_js_css_files'));
            
        ///////////////// DAILY SUMMARY /////////////////////////////////////////////
        $pagehook5 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Summary', 'wpdev-booking'), __('Summary', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-summary', array(&$this, 'content_of_summary_page')  );
        add_action("admin_print_scripts-" . $pagehook5 , array( &$this, 'add_admin_js_css_files'));
            
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S E R V A T I O N
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook2 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Add Booking', 'wpdev-booking'), __('Add Booking', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-reservation', array(&$this, 'content_of_edit_booking_page')  );
        add_action("admin_print_scripts-" . $pagehook2 , array( &$this, 'add_admin_js_css_files'));
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S O U R C E S     Management
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook4 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Resources', 'wpdev-booking'), __('Resources', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-resources', array(&$this, 'content_of_resource_page')  );
        add_action("admin_print_scripts-" . $pagehook4 , array( &$this, 'add_admin_js_css_files'));

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // S E T T I N G S
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook3 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Booking settings customizations', 'wpdev-booking'), __('Settings', 'wpdev-booking'), 'administrator',
                WPDEV_BK_FILE .'wpdev-booking-option', array(&$this, 'content_of_settings_page')  );
        add_action("admin_print_scripts-" . $pagehook3 , array( &$this, 'add_admin_js_css_files'));
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
    function add_admin_js_css_files() {
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

        ?> <!--  J a v a S c r i p t -->
        <script  type="text/javascript">
            var wpdev_bk_plugin_url = '<?php echo WPDEV_BK_PLUGIN_URL; ?>';

            // Check for correct URL based on Location.href URL, its need for correct aJax request
            var real_domain = window.location.href;
            var start_url = '';
            var pos1 = real_domain.indexOf('//'); //get http
            if (pos1 > -1 ) { start_url= real_domain.substr(0, pos1+2); real_domain = real_domain.substr(pos1+2);   }  //set without http
            real_domain = real_domain.substr(0, real_domain.indexOf('/') );    //setdomain
            var pos2 = wpdev_bk_plugin_url.indexOf('//');  //get http
            if (pos2 > -1 ) wpdev_bk_plugin_url = wpdev_bk_plugin_url.substr(pos2+2);    //set without http
            wpdev_bk_plugin_url = wpdev_bk_plugin_url.substr( wpdev_bk_plugin_url.indexOf('/') );    //setdomain
            wpdev_bk_plugin_url = start_url + real_domain + wpdev_bk_plugin_url;
            ///////////////////////////////////////////////////////////////////////////////////////

            var wpdev_bk_plugin_filename = '<?php echo WPDEV_BK_PLUGIN_FILENAME; ?>';
        </script>
        <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/js/datepick/jquery.datepick.js"></script>  <?php
        $locale = 'en_US'; // Load translation for calendar
        if ( ( !empty( $locale ) ) && ( substr($locale,0,2) !== 'en')  )
            if (file_exists(WPDEV_BK_PLUGIN_DIR. '/js/datepick/jquery.datepick-'. substr($locale,0,2) .'.js')) {
                ?> <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/js/datepick/jquery.datepick-<?php echo substr($locale,0,2); ?>.js"></script>  <?php
            }
        ?> <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/js/wpdev.bk.js"></script>  

        <!-- C S S -->
        <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/skins/traditional.css" rel="stylesheet" type="text/css" /> 
        <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/bs/css/bs.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/chosen/chosen.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/admin.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/bs/js/bs.min.js"></script>  
        <script type="text/javascript" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/interface/chosen/chosen.jquery.min.js"></script>
        <link href="<?php echo WPDEV_BK_PLUGIN_URL; ?>/css/client.css" rel="stylesheet" type="text/css" />  <?php
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
error_log($ds->toXml());
        echo $ds->toHtml();
    }

    /**
     * Write the contents of the booking resources page.
     */
    function content_of_resource_page(){

        if (isset($_GET['editResourceId'])) {

            $rpp = new ResourcePropertyPage($_GET['editResourceId']);

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
        echo $s->toHtml();
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
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (booking_id)
                    ) $charset_collate;";
            $wpdb->query($wpdb->prepare($simple_sql));
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
            $wpdb->query($wpdb->prepare($simple_sql));
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
            $wpdb->query($wpdb->prepare($simple_sql));
        }
            
        if ( false == $this->does_table_exist('resource_properties') ) { 
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."resource_properties (
                        property_id bigint(20) unsigned NOT NULL,
                        description varchar(20) NOT NULL,
                        PRIMARY KEY (property_id)
                    ) $charset_collate;";
            $wpdb->query($wpdb->prepare($simple_sql));
                
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
            $wpdb->query($wpdb->prepare($simple_sql));
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
            $wpdb->query($wpdb->prepare($simple_sql));
        }
            
        if ( false == $this->does_table_exist('allocation') ) {
            $simple_sql = "CREATE TABLE ".$wpdb->prefix ."allocation (
                        allocation_id bigint(20) unsigned NOT NULL auto_increment,
                        booking_id bigint(20) unsigned NOT NULL,
                        resource_id bigint(20) unsigned NOT NULL,
                        guest_name varchar(50) NOT NULL,
                        gender varchar(1) NOT NULL,
                        created_by varchar(20) NOT NULL,
                        created_date datetime NOT NULL,
                        last_updated_by varchar(20) NOT NULL,
                        last_updated_date datetime NOT NULL,
                        PRIMARY KEY (allocation_id),
                        FOREIGN KEY (booking_id) REFERENCES ".$wpdb->prefix ."booking(booking_id),
                        FOREIGN KEY (resource_id) REFERENCES ".$wpdb->prefix ."bookingresources(resource_id) 
                    ) $charset_collate;";
            $wpdb->query($wpdb->prepare($simple_sql));
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
            $wpdb->query($wpdb->prepare($simple_sql));
        }
            
        if( false == $this->does_routine_exist('walk_tree_path')) {
            $simple_sql = "CREATE FUNCTION walk_tree_path(p_resource_id BIGINT(20) UNSIGNED) RETURNS VARCHAR(255)
                -- walks the wp_bookingresources table from the given resource_id down to the root
                -- returns the path walked delimited with /
                BEGIN
                    DECLARE last_id BIGINT(20) UNSIGNED;
                    DECLARE parent_id BIGINT(20) UNSIGNED;
                    DECLARE return_val VARCHAR(255);
                        
                    SET return_val = p_resource_id;
                    SET parent_id = p_resource_id;
                    
                    WHILE parent_id IS NOT NULL DO
                        
                        SELECT parent_resource_id INTO parent_id
                        FROM wp_bookingresources
                        WHERE resource_id = parent_id;
                    
                        SET return_val = CONCAT(IFNULL(parent_id, ''), '/', return_val);
                    
                    END WHILE;
                        
                    RETURN return_val;
                    
                END;";
            $wpdb->query($wpdb->prepare($simple_sql));
        }
            
        if ( false == $this->does_table_exist('v_resources_sub1') ) {
            $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_resources_sub1 AS
                    SELECT resource_id, name, parent_resource_id, walk_tree_path(resource_id) AS path, resource_type, room_type
                    FROM ".$wpdb->prefix."bookingresources";
            $wpdb->query($wpdb->prepare($simple_sql));
        }

        if ( false == $this->does_table_exist('v_resources_by_path') ) {
            $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_resources_by_path AS
                    SELECT resource_id, name, parent_resource_id, path, resource_type, room_type,
                            LENGTH(path) - LENGTH(REPLACE(path, '/', '')) AS level,
                            (SELECT COUNT(*) FROM wp_v_resources_sub1 s1 WHERE s1.path LIKE CAST(CONCAT(s.path, '/%') AS CHAR) AND resource_type = 'bed') AS number_children,
                            (SELECT COUNT(*) FROM wp_v_resources_sub1 s1 WHERE (s1.path LIKE CAST(CONCAT(s.path, '/%') AS CHAR) OR s1.path = s.path) AND resource_type = 'bed') AS capacity
                      FROM ".$wpdb->prefix."v_resources_sub1 s
                     ORDER BY path";
// FIXME: remove number_children, rename capacity ==> number_beds
            $wpdb->query($wpdb->prepare($simple_sql));
        }

        if ( false == $this->does_table_exist('v_booked_capacity') ) {
            $simple_sql = "CREATE OR REPLACE VIEW ".$wpdb->prefix."v_booked_capacity (booking_date, resource_id, used_capacity) AS
                    SELECT bd.booking_date, alloc.resource_id, COUNT(*) used_capacity
                      FROM ".$wpdb->prefix."bookingdates bd
                      JOIN ".$wpdb->prefix."allocation alloc ON bd.allocation_id = alloc.allocation_id
                     GROUP BY bd.booking_date, alloc.resource_id";
            $wpdb->query($wpdb->prepare($simple_sql));
        }

        if ( false == $this->does_table_exist('v_resource_availability') ) {
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
            $wpdb->query($wpdb->prepare($simple_sql));
        }

        if ( false == $this->does_trigger_exista('trg_enforce_availability') ) {
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
            $wpdb->query($wpdb->prepare($simple_sql));
        }

        if ( false == $this->does_trigger_exista('trg_mv_resources_by_path_ins') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_ins
                -- this will update the materialized view
                -- whenever an insert is made on the underlying table
                AFTER INSERT ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    INSERT INTO ".$wpdb->prefix."mv_resources_by_path
                    SELECT * FROM ".$wpdb->prefix."v_resources_by_path
                        WHERE resource_id = NEW.resource_id;
                END";
            $wpdb->query($wpdb->prepare($simple_sql));
        }

        if ( false == $this->does_trigger_exista('trg_mv_resources_by_path_upd') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_upd
                -- this will update the materialized view
                -- whenever an update is made on the underlying table
                AFTER UPDATE ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    DELETE FROM ".$wpdb->prefix."mv_resources_by_path
                     WHERE resource_id = OLD.resource_id;

                    INSERT INTO ".$wpdb->prefix."mv_resources_by_path
                    SELECT * FROM ".$wpdb->prefix."v_resources_by_path
                     WHERE resource_id = NEW.resource_id;
                END";
            $wpdb->query($wpdb->prepare($simple_sql));
        }

        if ( false == $this->does_trigger_exista('trg_mv_resources_by_path_del') ) {
            $simple_sql = "CREATE TRIGGER ".$wpdb->prefix."trg_mv_resources_by_path_del
                -- this will update the materialized view
                -- whenever a delete is made on the underlying table
                AFTER DELETE ON ".$wpdb->prefix."bookingresources FOR EACH ROW 
                BEGIN
                    DELETE FROM ".$wpdb->prefix."mv_resources_by_path
                     WHERE resource_id = OLD.resource_id;
                END";
            $wpdb->query($wpdb->prepare($simple_sql));
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
