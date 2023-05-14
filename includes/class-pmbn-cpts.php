<?php
/**
 * Custom post types
 *
 * @package WordPress
 * @since PmBN 1.0.0
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class PmBNCpts {
	
	function __construct(){
		$this->load_dependencies();
		$this->init();
	}
	
	private function load_dependencies(){
		
	}

	private function init(){
		$this->add_actions();
		$this->add_filters();
	}
	
	private function add_actions(){
		add_action( 'init', [$this, 'register_cpts'] );
	}
	
	private function add_filters(){
	}
	
	public function register_cpts(){
		register_post_type( 'building-navigators', [
			'labels' => [
				'name' 			=> __('Buildings', PM_BN_TEXTDOMAIN),
				'singular_name' => __('Building navigator', PM_BN_TEXTDOMAIN),
				'add_new_item' => __('Add new building navigator', PM_BN_TEXTDOMAIN),
				'edit_item' => __('Edit building navigator', PM_BN_TEXTDOMAIN),
				'new_item' => __('New building navigator', PM_BN_TEXTDOMAIN),
				'view_item' => __('View building navigator', PM_BN_TEXTDOMAIN),
				'view_items' => __('View building navigators', PM_BN_TEXTDOMAIN),
				'search_items' => __('Search building navigators', PM_BN_TEXTDOMAIN),
				'not_found' => __('Building navigator not found', PM_BN_TEXTDOMAIN),
				'not_found_in_trash' => __('Building navigator not found in trash', PM_BN_TEXTDOMAIN),
				'parent_item_colon' => __('Parent building navigator:', PM_BN_TEXTDOMAIN),
				'all_items' => __('All buildings', PM_BN_TEXTDOMAIN),
				'archives' => __('Archive building navigators', PM_BN_TEXTDOMAIN),
				'insert_into_item' => __('Insert into building navigator', PM_BN_TEXTDOMAIN),
				'uploaded_to_this_item' => __('Uploaded to this building navigator', PM_BN_TEXTDOMAIN),
			],
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => true,
			'show_in_menu' => true,
			'has_archive' => false,
			'supports'	=> ['title'],
			'menu_icon' 	=> 'dashicons-building',
		]);
	}
}