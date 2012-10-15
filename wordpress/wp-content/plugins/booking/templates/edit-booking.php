<?php 
    // user must be logged in to access this page
    if ( false === is_user_logged_in() ) { 
        header( 'Location: '.home_url()."/wp-login.php?redirect_to=".urlencode($_SERVER['REQUEST_URI']) ) ;

    } else {
        get_header(); 

        if (false === isset($_SESSION['WP_HOSTELBACKOFFICE'])) {
            $_SESSION['WP_HOSTELBACKOFFICE'] = new WP_HostelBackoffice();
        }
        $_SESSION['WP_HOSTELBACKOFFICE']->content_of_edit_booking_page(); 

        get_footer(); 
    }
?>
