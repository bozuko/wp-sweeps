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