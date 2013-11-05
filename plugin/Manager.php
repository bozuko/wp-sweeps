<?php

class Sweeps_Manager extends Snap_Wordpress_Plugin
{
    public function __construct()
    {
        parent::__construct();
        // remove everything if we are not admin
        add_role('sweeps_manager', 'Sweepstakes Manager', array(
            'read'          => true,
            'manage_sweeps' => true
        ));
        
        get_role('sweeps_manager')->add_cap('manage_sweeps');
        get_role('administrator')->add_cap('manage_sweeps');
    }
    
    public function isManager()
    {
        return current_user_can('manage_sweeps') && !current_user_can('manage_options');
    }
    
    /**
     * @wp.filter
     */
    public function login_redirect($redirect_to, $requested, $user)
    {
        if( !is_wp_error( $user ) && $user->has_cap('manage_sweeps') && !$user->has_cap('manage_options') ){
            $redirect_to = 'wp-admin/admin.php?page=sweeps';
        }
        return $redirect_to;
    }
    
    /**
     * @wp.action               admin_bar_menu
     * @wp.priority             1
     */
    public function add_bozuko_logo( &$wp_admin_bar )
    {
        $wp_admin_bar->add_node(array(
            'title' => '<span class="bozuko-ab"></span>',
            'id'    => 'bozuko-logo',
            'href'  => 'https://bozuko.com'
        ));
    }
    
    
    /**
     * @wp.action               admin_bar_menu
     * @wp.priority             1000
     */
    public function remove_wordpress_admin_menu_item( &$wp_admin_bar )
    {
        $wp_admin_bar->remove_node('wp-logo');
    }
    
    /**
     * @wp.action
     */
    public function admin_menu()
    {
        // TODO - maybe add a separator
        add_menu_page('Sweepstakes', 'Sweepstakes', 'manage_sweeps', 'sweeps', array(&$this, 'sweeps_page'),
            SWEEPS_URL.'/resources/images/bozuko-icon.png', 25
        );
        
        if( $this->isManager() ){
            // we want to remove dashboard...
            #remove_menu_page('index.php');
        }
    }
    
    /**
     * @wp.action
     */
    public function wp_dashboard_setup()
    {
        // add our own dashboard widgets
        wp_add_dashboard_widget('sweeps_overview', 'Sweepstakes Overview', array(&$this, 'overview_widget'));
        wp_add_dashboard_widget('sweeps_entries', 'Entries Overview', array(&$this, 'entries_widget'));
        
        global $wp_meta_boxes;
        $entries = $wp_meta_boxes['dashboard']['normal']['core']['sweeps_entries'];
        unset( $wp_meta_boxes['dashboard']['normal']['core']['sweeps_entries']);
        $wp_meta_boxes['dashboard']['side']['core']['sweeps_entries'] = $entries;
        
        // float our stuff to the top...
        $this->dashboard_widget_to_top('normal', 'sweeps_overview');
        $this->dashboard_widget_to_top('side', 'sweeps_entries');
    }
    
    public function dashboard_widget_to_top($where, $id)
    {
        global $wp_meta_boxes;
        // float our stuff to the top...
        $boxes = $wp_meta_boxes['dashboard'][$where]['core'];
        $top = array($id => $boxes[$id] );
        unset( $boxes[$id] );
        $wp_meta_boxes['dashboard'][$where]['core'] = array_merge( $top, $boxes );
    }
    
    public function overview_widget()
    {
        $GLOBALS['campaigns'] = Snap::singleton('Sweeps_Campaign')->get_all();
        Snap_Wordpress_Template::load('sweeps', 'back/dashboard/overview');
    }
    
    public function entries_widget()
    {
        // get the total entries
        $q = new WP_Query(array('post_type' => 'sweep_entry'));
        $GLOBALS['total_entries'] = $q->found_posts;
        Snap_Wordpress_Template::load('sweeps', 'back/dashboard/entries');
    }
    
    /**
     * @wp.ajax
     */
    public function get_entry_counts()
    {
        $this->returnJSON( Snap::singleton('Sweeps_Entry')->get_counts(
            @$_REQUEST['start'], @$_REQUEST['end'], @$_REQUEST['campaign']
        ));
    }
    
    public function sweeps_page()
    {
        switch( @$_REQUEST['view'] ){
            
            case 'download':
                
                $campaign = get_post( @$_REQUEST['campaign'] );
                $view = new Snap_Wordpress_View('sweeps', 'back/download');
                $view->set('campaign', $campaign );
                $view->render();
                return;
            
            case 'campaign':
            default:
            
                global $wpdb;
                #wp_enqueue_script('google-jsapi', 'https://www.google.com/jsapi' );
                wp_enqueue_script('highstock', SWEEPS_URL.'/resources/highstock/js/highstock.js', array('jquery'));
                wp_enqueue_script('sweeps-dashboard', SWEEPS_URL.'/resources/js/dashboard.js', array('highstock'));
                $GLOBALS['campaigns'] = Snap::singleton('Sweeps_Campaign')->get_all();
                $GLOBALS['counts'] = Snap::singleton('Sweeps_Entry')->get_counts();
                Snap_Wordpress_Template::load('sweeps', 'back/sweepstakes');
                
        }
    }
}