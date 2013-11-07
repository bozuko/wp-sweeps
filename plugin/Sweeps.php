<?php

class Sweeps extends Snap_Wordpress_Plugin
{
    function __construct()
    {
        parent::__construct();
        global $sweeps_campaign;
        
        // add our image sizes
        add_image_size('sweeps810', 810, 1000, false);
        add_image_size('sweeps520', 520, 1000, false);
        add_image_size('sweeps400', 400, 1000, false);
        
        add_image_size('og', 200, 200, true);
        
        $sweeps_campaign = Snap::singleton( 'Sweeps_Campaign' );
        Snap::singleton( 'Sweeps_Entry' );
        Snap::singleton( 'Sweeps_Prize' );
        
        // create our manager plugin
        Snap::singleton( 'Sweeps_Manager' );
        
        
        Snap_Wordpress_Template::registerPath('sweeps', SWEEPS_DIR.'/template' );
        Snap_Wordpress_Template::registerPath('sweeps', get_stylesheet_directory().'/sweeps');
        
        do_action('sweeps_template_init');
        
        Snap_Wordpress_Form_Validator_Factory::register(
            'uniqueEmail', 'Sweeps_Validator_UniqueEmail'
        );
        
        Snap_Wordpress_Form_Validator_Factory::register(
            'uniquePhone', 'Sweeps_Validator_UniquePhone'
        );
    }
    
    /**
     * @wp.filter
     */
    public function cron_schedules( $schedules )
    {
        $schedules['ten_seconds'] = array(
            'interval'  => 15,
            'display'   => __('10 Seconds')
        );
        return $schedules;
    }
    
    /**
     * @wp.action       save_post
     * @wp.priority     200
     */
    public function update_cron( $post_id )
    {
        if( get_post_type( $post_id ) != 'sweep_campaign' ) return;
        $notifications = get_post_meta($post_id, 'notifications');
        if( count($notifications) && $notifications[0] == 'daily' ){
            if( !wp_next_scheduled('sweeps_send_summary', array( $post_id ) ) ){
                $tomorrow = date('Y-m-d 00:01:00', strtotime( 'tomorrow', time() + (get_option('gmt_offset') * HOUR_IN_SECONDS) ));
                wp_schedule_event(strtotime($tomorrow)+ (get_option('gmt_offset')*HOUR_IN_SECONDS), 'daily', 'sweeps_send_notification', array( $post_id ) );
                //wp_schedule_event(current_time('timestamp'), 'ten_seconds', 'sweeps_send_summary', array( $post_id ) );
            }
        }
        else {
            if( wp_next_scheduled('sweeps_send_summary', array( $post_id ) ) ){
                wp_clear_scheduled_hook('sweeps_send_summary', array($post_id) );
            }
        }
    }
    
    /**
     * @wp.action       init
     */
    public function test_send_summary()
    {
        if( !($id = @$_GET['test_send_summary']) ) return;
        $this->sweeps_send_summary( $id );
        exit;
    }
    
    /**
     * @wp.action       sweeps_send_summary
     */
    public function sweeps_send_summary( $campaign_id )
    {
        $custom = get_post_custom( $campaign_id );
        if( !$custom || !is_array($custom) || @$custom['notifications'][0] != 'daily' ){
            return;
        }
        
        $emails = @$custom['notificationEmails'];
        if( !$emails ){
            return;
        }
        
        $emails = explode(', ', $emails[0]);
        $title = get_the_title( $campaign_id );
        
        $midnight = strtotime( 'midnight', time() + (get_option('gmt_offset') * HOUR_IN_SECONDS) );
        $daily = Sweeps_Campaign::get_entry_count( $campaign_id, date('Y-m-d H:i:s', strtotime( '-1 day', $midnight)));
        $weekly = Sweeps_Campaign::get_entry_count( $campaign_id, date('Y-m-d H:i:s', strtotime( '-1 week', $midnight)));
        $total = Sweeps_Campaign::get_entry_count( $campaign_id );
        
        $message = array(
            "Daily summary for the `{$title}` promotion:",
            "",
            "{$daily} entries yesterday",
            "{$weekly} entries in the last week",
            "{$total} entries total",
            "",
            "-D.L. Blair Digital Promotions"
        );
        
        wp_mail( $emails, 'Daily Entry Summary for '.$title, implode("\n", $message) );
    }
    
    public static function log()
    {
        $args = func_get_args();
        
        foreach($args as $i => $arg){
            if( !is_string($arg) ) $args[$i] = print_r($arg,1);
        }
        
        $str = implode(', ', $args);
        $date = date('Y-m-d H:i:s');
        $ip = $_SERVER['REMOTE_ADDR'];
        $log = "$date [$ip] - $str\n";
        $dir = wp_upload_dir();
        $dir = $dir['path'];
        file_put_contents(  $dir.'/log.txt', $log, FILE_APPEND );
    }
    /**
     * @wp.action               wp
     * @wp.priority             0
     */
    public function pseudo_ajax_header()
    {
        if( @$_REQUEST['_ajax_'] ){
            $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        }
    }
    
    /**
     * @wp.filter
     */
    public function login_headerurl()
    {
        return 'https://bozuko.com';
    }
    
    /**
     * @wp.filter
     */
    public function login_headertitle()
    {
        return 'Powered by Bozuko Sweepstakes';
    }
    
    /**
     * @wp.action
     */
    public function login_head()
    {
        ?>
<style type="text/css">
.login h1 a {
    background-image: url(<?= SWEEPS_URL ?>/resources/images/logo.png);
    height: 125px;
    background-size: auto auto;
}
</style>
        <?php
    }
    
    /**
     * @wp.filter
     */
    public function wp_mail_from()
    {
        return 'no-reply@dlblairsweeps.com';
    }
    
    /**
     * @wp.filter
     */
    public function wp_mail_from_name()
    {
        return 'D.L. Blair Digital Promotions';
    }
    
    /**
     * @wp.action
     */
    function init()
    {
        if( !@session_id() ) @session_start();
        // how about some new image sizes?
        add_image_size('prize', 400, 400, true);
    }
    
    /**
     * @wp.action
     */
    function admin_init()
    {
        wp_enqueue_style('sweeps-admin', SWEEPS_URL.'/resources/css/admin.css');
    }
    
    /**
     * @wp.action
     */
    function wp_dashboard_setup()
    {
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_secondary', 'dashboard', 'side');
    }
    
    /*********************************************************************************
     *
     * The following two functions are to allow filtering by meta value in the
     * admin lists (affects all)
     * 
     *********************************************************************************/
    
    /**
     * @wp.filter           posts_where
     */
    function where( $where ) {
        global $wp_query, $wpdb;
       
        if( !is_admin() || !isset( $wp_query->query_vars['s'] ) || !$wp_query->query_vars['s'] )
            return $where;
       
        $s = $wp_query->query_vars['s'];
       
        if(
            // If the where string contains what i'm looking for
            strpos( $where , 'AND (((' ) != false &&
            // Make sure there's no other matches in the string
            strpos( substr( $where , 10 ) , 'AND (((' ) == false )
        {       
            // Was either strpos and str_replace or preg_*** functions, which are heavier ...
            $where = str_replace( 'AND (((' , "AND (((meta_search.meta_value LIKE '%$s%') OR (" , $where );
        }
        return $where;
    }
        
    /**
     * @wp.filter           posts_join
     */
    function join( $join ) {
        global $wp_query, $wpdb;

        if( is_admin() && isset( $wp_query->query_vars['s'] ) && $wp_query->query_vars['s'] ) {
            $join .= " LEFT JOIN " . $wpdb->postmeta . " meta_search ON " . $wpdb->posts . ".ID = meta_search.post_id ";
        }
        return $join;
    }
    
    /**
     * @wp.filter           posts_groupby
     */
    function groupby( $groupby ) {
        global $wp_query, $wpdb;
        
        if( !is_admin() || !isset( $wp_query->query_vars['s'] ) || !$wp_query->query_vars['s'] )
            return $groupby;
        
        // we need to group on post ID
        $mygroupby = "{$wpdb->posts}.ID";

        if( preg_match( "/$mygroupby/", $groupby )) {
            // grouping we need is already there
            return $groupby;
        }

        if( !strlen(trim($groupby))) {
            // groupby was empty, use ours
            return $mygroupby;
        }

        // wasn't empty, append ours
        return $groupby . ", " . $mygroupby;
    }
    
}