<?php 
/*
*
*	***** poschapin-woocommerce *****
*
*	This file initializes all POSCHAPINWOO Core components
*	
*/
// If this file is called directly, abort. //
if ( ! defined( 'WPINC' ) ) {die;} // end if
// Define Our Constants
define('POSCHAPINWOO_CORE_INC',dirname( __FILE__ ).'/assets/inc/');
define('POSCHAPINWOO_CORE_IMG',plugins_url( 'assets/img/', __FILE__ ));
define('POSCHAPINWOO_CORE_CSS',plugins_url( 'assets/css/', __FILE__ ));
define('POSCHAPINWOO_CORE_JS',plugins_url( 'assets/js/', __FILE__ ));
/*
*
*  Register CSS
*
*/
function poschapinwoo_register_core_css(){
//wp_enqueue_style('poschapinwoo-core', POSCHAPINWOO_CORE_CSS . 'poschapinwoo-core.css',null,time(),'all');
};
add_action( 'wp_enqueue_scripts', 'poschapinwoo_register_core_css' );    
/*
*
*  Register JS/Jquery Ready
*
*/
function poschapinwoo_register_core_js(){
// Register Core Plugin JS	
//wp_enqueue_script('poschapinwoo-core', POSCHAPINWOO_CORE_JS . 'poschapinwoo-core.js','jquery',time(),true);
};
add_action( 'wp_enqueue_scripts', 'poschapinwoo_register_core_js' );    
/*
*
*  Includes
*
*/ 
// Load the Functions
if ( file_exists( POSCHAPINWOO_CORE_INC . 'poschapinwoo-core-functions.php' ) ) {
	require_once POSCHAPINWOO_CORE_INC . 'poschapinwoo-core-functions.php';
}     
// Load the ajax Request
if ( file_exists( POSCHAPINWOO_CORE_INC . 'poschapinwoo-ajax-request.php' ) ) {
	require_once POSCHAPINWOO_CORE_INC . 'poschapinwoo-ajax-request.php';
} 
// Load the Shortcodes
if ( file_exists( POSCHAPINWOO_CORE_INC . 'poschapinwoo-shortcodes.php' ) ) {
	require_once POSCHAPINWOO_CORE_INC . 'poschapinwoo-shortcodes.php';
}