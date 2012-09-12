<?php get_header(); ?>

<?php 
    if (false === isset($_SESSION['WP_HOSTELBACKOFFICE'])) {
        $_SESSION['WP_HOSTELBACKOFFICE'] = new WP_HostelBackoffice();
    }
    $_SESSION['WP_HOSTELBACKOFFICE']->content_of_edit_booking_page(); 
?>

<?php get_footer(); ?>
