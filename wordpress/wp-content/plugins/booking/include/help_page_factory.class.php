<?php

/**
 * Facade for generating pre-defined help pages.
 */
class HelpPageFactory {

    /** 
     * Default constructor.
     */
    function HelpPageFactory() {
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
error_log("inserted page with id: $post_id");
            update_post_meta($post_id, '_wp_page_template', 'sidebar-page.php'); // add sidebar to page
        } 
        return $post_id;
    }

    /**
     * Creates the help pages and help submenu pages.
     * Returns post id of the newly created parent help page, 0 on failure.
     */
    function createHelpPages() {

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
        $pg_ids_to_del = array();
        if ($pg != null) {
            $pg_ids_to_del[] = $pg->ID;

            // find the pages under Help
            $args = array(
	            'child_of' => $pg->ID,
	            'post_type' => 'page',
	            'post_status' => 'publish'
            ); 
            foreach (get_pages($args) as $p) {
                $pg_ids_to_del[] = $p->ID;
            }

            // we physically delete them in reverse order so children are removed before their parent
            foreach (array_reverse($pg_ids_to_del) as $pid) {
                wp_delete_post($pid, true);
            }
        } 
    }


}

?>
