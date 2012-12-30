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

        $help_id = $this->createReadOnlyPage('help', 'Help', 

        // on previous success, create help sub-pages
        if ($help_id > 0) {

            $pages_id = $this->createReadOnlyPage('pages', 'Pages', 
              $help_id);


            // create individual help pages
            if ($pages_id > 0) {
                $post_id = $this->createReadOnlyPage('add-edit-booking', 'Add/Edit Booking', 
                  $pages_id
                );

                $post_id = $this->createReadOnlyPage('allocations', 'Allocations', 
                  $pages_id
                );

                $post_id = $this->createReadOnlyPage('bookings', 'Bookings', 
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