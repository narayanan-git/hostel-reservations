<?php

/**
 * Facade for generating pre-defined wordpress pages.
 */
class PageFactory {

    /** 
     * Default constructor.
     */
    function PageFactory() {
    }

    /**
     * Creates a locked page with the given title and contents.
     * $name : name (slug) of new page
     * $title : title of page
     * $content : full contents of page
     * $parent_post_id : (optional)  parent page post id
     * $menu_order : (optional) order if page is in a menu
     * Returns new post id
     */
    function createReadOnlyPage($name, $title, $content, $parent_post_id = 0, $menu_order = 0) {
        $my_post = array(
          'post_title'    => $title,
          'post_content'  => $content,
          'post_status'   => 'publish',
          'post_author'   => 1,
          'post_type'     => 'page',
          'post_name'     => $name,
          'post_parent'   => $parent_post_id,
          'menu_order'    => $menu_order,
          'comment_status' => 'closed',
          'ping_status'   => 'closed'
        );

        // Insert the page into the database
        $post_id = wp_insert_post( $my_post, true );

        if (is_wp_error($post_id)) {
            error_log($post_id->get_error_message());
            $post_id = 0;
        } else {
            update_post_meta($post_id, '_wp_page_template', 'sidebar-page.php'); // add sidebar to page
        } 
        return $post_id;
    }

    /**
     * Create placeholder pages which will be replaced with their respective template pages.
     */
    function createTemplatePages() {

        // only create if not exists
        if (get_page_by_path('edit-booking') == null) {
            $new_booking_post_id = $this->createReadOnlyPage('edit-booking', 'New Booking', 'template content goes here', 0, 70);
        }

        if (get_page_by_path('admin') == null) {
            $admin_id = $this->createReadOnlyPage('admin', 'Admin', 'template content goes here', 0, 80);
            if ($admin_id > 0) {
                $post_id = $this->createReadOnlyPage('allocations', 'Allocations', 'template content goes here', $admin_id, 10);
                $post_id = $this->createReadOnlyPage('bookings', 'Bookings', 'template content goes here', $admin_id, 20);
                $post_id = $this->createReadOnlyPage('summary', 'Daily Summary', 'template content goes here', $admin_id, 30);
                $post_id = $this->createReadOnlyPage('resources', 'Resources', 'template content goes here', $admin_id, 40);
                $post_id = $this->createReadOnlyPage('housekeeping', 'Housekeeping', 'template content goes here', $admin_id, 50);
            }
        }
    }

    /**
     * Forcefully removes all template placeholder pages created as part of createTemplatePages().
     */
    function deleteTemplatePages() {
        
        // find the parent page id for New/Edit Booking
        $pg = get_page_by_path('edit-booking');
        if ($pg != null) {
            self::deletePages($pg->ID);
        } 

        // find the parent page id for Admin
        $pg = get_page_by_path('admin');
        if ($pg != null) {
            self::deletePages($pg->ID);
        } 
    }

    /**
     * Creates the help pages and help submenu pages.
     * Returns post id of the newly created parent help page, 0 on failure.
     */
    function createHelpPages() {

        // don't do anything if it already exists
        $help_page = get_page_by_path('help');
        if ( $help_page != null) {
            return $help_page->ID;
        }

        $hp = new HelpPage();
        $help_id = $this->createReadOnlyPage('help', 'Help', $hp->toHtml('help'), 0, 100);

        // on previous success, create help sub-pages
        if ($help_id > 0) {

            $pages_id = $this->createReadOnlyPage('pages', 'Pages', $hp->toHtml('pages'), $help_id, 10);

            // create individual help pages
            if ($pages_id > 0) {
                $post_id = $this->createReadOnlyPage('add-edit-booking', 'Add/Edit Booking', 
                    $hp->toHtml('add-edit-booking'), $pages_id, 10);

                $post_id = $this->createReadOnlyPage('allocations', 'Allocations', 
                    $hp->toHtml('allocations'), $pages_id, 20);

                $post_id = $this->createReadOnlyPage('bookings', 'Bookings',                     $hp->toHtml('bookings'), $pages_id, 30);

                $post_id = $this->createReadOnlyPage('daily-summary', 'Daily Summary',                     $hp->toHtml('daily-summary'), $pages_id, 40);

                $post_id = $this->createReadOnlyPage('resources', 'Resources',                     $hp->toHtml('resources'), $pages_id, 50);
            }

            $faq_id = $this->createReadOnlyPage('faq', 'FAQ', $hp->toHtml('faq'), $help_id, 20);

            // create individual FAQ pages
            if ($faq_id > 0) {
                $post_id = $this->createReadOnlyPage('how-do-i-add-a-new-booking', 'How do I add a new booking?', 
                    $hp->toHtml('how-do-i-add-a-new-booking'), $faq_id, 10);

                $post_id = $this->createReadOnlyPage('how-do-i-check-in-a-guest', 'How do I check-in a guest?',                     $hp->toHtml('how-do-i-check-in-a-guest'), $faq_id, 20);

                $post_id = $this->createReadOnlyPage('how-do-i-checkout-a-single-guest', 'How do I checkout a single guest?',                     $hp->toHtml('how-do-i-checkout-a-single-guest'), $faq_id, 30);

                $post_id = $this->createReadOnlyPage('how-do-i-checkout-all-guests-for-a-booking', 'How do I checkout all guests for a booking?',                     $hp->toHtml('how-do-i-checkout-all-guests-for-a-booking'), $faq_id, 40);

                $post_id = $this->createReadOnlyPage('how-do-i-add-additional-nights-to-an-existing-booking', 'How do I add additional nights to an existing booking?',                     $hp->toHtml('how-do-i-add-additional-nights-to-an-existing-booking'), $faq_id, 50);

                $post_id = $this->createReadOnlyPage('how-do-i-cancel-nights-from-an-existing-booking', 'How do I cancel nights from an existing booking?',                     $hp->toHtml('how-do-i-cancel-nights-from-an-existing-booking'), $faq_id, 60);

                $post_id = $this->createReadOnlyPage('how-do-i-change-the-room-allocation-for-a-booking', 'How do I change the room allocation for a booking?',                     $hp->toHtml('how-do-i-change-the-room-allocation-for-a-booking'), $faq_id, 70);

                $post_id = $this->createReadOnlyPage('how-do-i-deactivate-a-room-for-a-particular-set-of-dates', 'How do I deactivate a room for a particular set of dates?',                     $hp->toHtml('how-do-i-deactivate-a-room-for-a-particular-set-of-dates'), $faq_id, 80);
            }
        }
        return $help_id;
    }

    /**
     * Forcefully removes all help pages created in createHelpPages().
     */
    function deleteHelpPages() {

        // find the parent page id for Help
        $pg = get_page_by_path('help');
        if ($pg != null) {
            self::deletePages($pg->ID);
        } 
    }

    /**
     * Deletes a given page by id.
     * $page_id : ID of post/page to delete
     * $include_children : true/false (default true) to delete all child pages from given page
     */
    function deletePages($page_id, $include_children = true) {

        $pg_ids_to_del = array($page_id);

        if ($include_children) {
            // find the descendent pages
            $args = array(
	            'child_of' => $page_id,
	            'post_type' => 'page',
	            'post_status' => 'publish'
            ); 
            foreach (get_pages($args) as $p) {
                $pg_ids_to_del[] = $p->ID;
            }
        }

        // we physically delete them in reverse order so children are removed before their parent
        foreach (array_reverse($pg_ids_to_del) as $pid) {
            wp_delete_post($pid, true);
        }
    }

}

?>
