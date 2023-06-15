<?php
/*
Plugin Name: PM Building Navigator
Plugin URI: https://miklospomsar.com
Description: With this plugin you can create building navigator interfaces from schematic SVG building graphics. To display the building's apartments data in a simple and user-friendly way.
Version: 1.0.3
Author: Pomsár Miklós
Author URI: https://miklospomsar.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

if( !class_exists('PmBuildingNavigator') ) :

define('PM_BN_TEXTDOMAIN','pmbn');
define('PM_BN_URL', plugin_dir_url( __FILE__ ));
define('PM_BN_DIR', plugin_dir_path( __FILE__ ));
if(!defined ( 'DS' ) ){
	define ( 'DS' , DIRECTORY_SEPARATOR );
}

class PmBuildingNavigator {
	var $notices = [];
	var $deps_ok = true;
	
	function __construct() {
		$this->settings = [
			'version' => '1.0.3',
			'dev' => false,
		];
		
		$this->load_includes();
		$this->init();
	}
	
	private function load_includes(){		
		
		require_once(PM_BN_DIR.'/includes/class-pmbn-cpts.php');
		require_once(PM_BN_DIR.'/includes/class-pmbn-scripts.php');
		require_once(PM_BN_DIR.'/includes/class-pmbn-navigator.php');
		
	}
	
	private function init(){
		//load textdomain early (it won't take user language settings)
		load_plugin_textdomain( PM_BN_TEXTDOMAIN, false, plugin_basename( dirname( __FILE__ ) ) . '/lang' );
		
		$this->add_actions();
		$this->add_filters();
		
		//Scripts
		$this->PmBNScripts = new PmBNScripts();
		
		//Custom Post types
		$this->PmBNCpts = new PmBNCpts();
		
		//Editor
		$this->PmBNNavigator = new PmBNNavigator();
		
	}
	
	private function add_actions(){
		add_action( 'init', array( $this, 'load_text_domain' ), 5 );
		add_action( 'admin_init', [$this, 'init_plugin'] );
		add_action( 'admin_notices', [$this, 'render_notices'] );
	}
	
	private function add_filters(){
		
	}
	
	public function init_plugin(){
		$this->check_dependencies();
	}
	
	public function load_text_domain(){
		//set text domain
		unload_textdomain( PM_BN_TEXTDOMAIN );
		$mofile = PM_BN_TEXTDOMAIN . '-' . determine_locale() . '.mo';
		$load_textdomain_success = load_textdomain( PM_BN_TEXTDOMAIN, WP_LANG_DIR . '/plugins/' . $mofile );
		if(!$load_textdomain_success){
			$load_textdomain_success = load_textdomain( PM_BN_TEXTDOMAIN, PM_BN_DIR . '/lang/' . $mofile );
		}
	}
	
	private function check_dependencies(){
		
	}
	
	public function render_notices(){
		foreach($this->notices as $notice){
			$classes = 'notice notice-'.$notice['type'];
			$classes .= ($notice['is_dismissible'])? ' is-dismissible' : '';
			
			printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $classes ), $notice['message'] );
		}
	}
	
	public function recursive_parse_args( $args, $defaults ) {
        $new_args = (array) $defaults;
 
        foreach ( $args as $key => $value ) {
            if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
                $new_args[ $key ] = $this->recursive_parse_args( $value, $new_args[ $key ] );
            }
            else {
                $new_args[ $key ] = $value;
            }
        }
 
        return $new_args;
    }
}

// initialize
$PmBuildingNavigator = new PmBuildingNavigator();
do_action('pmbn_init', $PmBuildingNavigator);

function pmbn_plugin_activate(){ 
	do_action('pmbn_plugin_activated');
}
register_activation_hook( __FILE__, 'pmbn_plugin_activate' );

// class_exists check
endif;



if(!function_exists('debug')){
	function debug($var = null){
		if(is_array($var) || is_object($var)){
			echo '<pre>';
			print_r($var);
			echo '</pre>';
		}
		else {
			echo '<pre>';
			var_dump($var);
			echo '</pre>';
		}
	}
}