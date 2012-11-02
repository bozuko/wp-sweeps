<?php
/*
Plugin Name: Sweeps
Plugin URI: http://bozuko.com
Description: Bozuko Sweeps is an easy to use plugin to create Sweepstakes for Facebook and standalone
Version: 1.0.1a
Author: Bozuko
Author URI: http://bozuko.com
License: GPLv2 or later
*/

#error_reporting(E_ALL);


#error_reporting( E_ALL );
#if( !defined('SAVEQUERIES') ) define('SAVEQUERIES', true );


add_action('plugins_loaded', 'sweeps_launch');
function sweeps_launch()
{
    
    if( !class_exists('Snap') ){
        // The Snap library is required
        function sweeps_snap_required(){
            echo '<div class="error"><p>The Sweepstakes Plugin requires the <a href="https://github.com/fabrizim/Snap">Snap Library</a>. Please download the zip file and install.</p></div>';
        }
        add_action( 'admin_notices', 'sweeps_snap_required' );
        return;
    }

    define( 'SWEEPS_DIR', dirname(__FILE__) );
    define( 'SWEEPS_URL', plugins_url( '', __FILE__ ) );
    
    define( 'SWEEPS_TEMPLATE_DIR', SWEEPS_DIR.'/template');
    define( 'SWEEPS_TEMPLATE_URL', SWEEPS_URL.'/template');
    
    require_once( SWEEPS_DIR . '/lib/facebook/src/facebook.php' );
    
    Snap_Loader::register( 'Sweeps', SWEEPS_DIR . '/plugin' );
    Snap::singleton('Sweeps');
}
