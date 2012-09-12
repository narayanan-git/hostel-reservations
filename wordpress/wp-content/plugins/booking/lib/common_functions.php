<?php
    if (!function_exists ('debuge')) {
        function debuge() {
            $numargs = func_num_args();
            $var = func_get_args();
            $makeexit = is_bool($var[count($var)-1])?$var[count($var)-1]:false;
            echo "<div style=''><pre class='prettyprint linenums' style=''>";
            print_r ( $var );
            echo "</pre></div>";
            if ($makeexit) {
                echo '<div style="font-size:18px;float:right;">' . get_num_queries(). '/'  . timer_stop(0, 3) . 'qps</div>';
                exit;
            }
        }
    }

    if (!function_exists ('debugq')) {
        function debugq() {
                echo '<div style="font-size:18px;float:right;">' . get_num_queries(). '/'  . timer_stop(0, 3) . 'qps</div>';
        }
    }

    /**
     * Interesting... pulls news data from external site.
     */
    define ('OBC_CHECK_URL', 'http://wpbookingcalendar.com/');

    function wpdev_ajax_check_bk_news(){

        $v=array();
        if (class_exists('wpdev_bk_personal'))            $v[] = 'wpdev_bk_personal';
        if (class_exists('wpdev_bk_biz_s'))        $v[] = 'wpdev_bk_biz_s';
        if (class_exists('wpdev_bk_biz_m'))   $v[] = 'wpdev_bk_biz_m';
        if (class_exists('wpdev_bk_biz_l'))          $v[] = 'wpdev_bk_biz_l';
        if (class_exists('wpdev_bk_multiuser'))      $v[] = 'wpdev_bk_multiuser';

        $obc_settings = array();
        $ver = get_bk_option('bk_version_data');
        if ( $ver !== false ) { $obc_settings = array( 'subscription_key'=>maybe_serialize($ver) ); }
        
        $params = array(
                    'action' => 'get_news',
                    'subscription_email' => isset($obc_settings['subscription_email'])?$obc_settings['subscription_email']:false,
                    'subscription_key'   => isset($obc_settings['subscription_key'])?$obc_settings['subscription_key']:false,
                    'bk' => array('bk_ver'=>WPDEV_BK_VERSION, 'bk_url'=>WPDEV_BK_PLUGIN_URL,'bk_dir'=>WPDEV_BK_PLUGIN_DIR, 'bk_clss'=>$v),
                    'siteurl'            => get_option('siteurl'),
                    'siteip'            => $_SERVER['SERVER_ADDR'],
                    'admin_email'        => get_option('admin_email')
        );

        $request = new WP_Http();
        $result  = $request->request( OBC_CHECK_URL . 'info/', array(
            'method' => 'POST',
            'timeout' => 15,
            'body' => $params
            ));

        if (!is_wp_error($result) && ($result['response']['code']=='200') && (true) ) {

           $string = ($result['body']);                                         //$string = str_replace( "'", '&#039;', $string );
           echo $string;

        } else  /**/
            { // Some error appear
            echo '<div id="bk_errror_loading">';
            if (is_wp_error($result))  echo $result->get_error_message();
            else                       echo $result['response']['message'];
            echo '</div>';
            echo ' <script type="text/javascript"> ';
            echo '    document.getElementById("bk_news").style.display="none";';
            echo '    jQuery("#bk_news_section").animate({opacity:1},3000).slideUp(1500);';
            echo ' </script> ';
        }

    }

?>
