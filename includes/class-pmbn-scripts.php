<?php
/**
 * Load plugin scripts functions
 *
 * @package WordPress
 * @since PmCP 1.0.0
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class PmBNScripts {

	public $JSData = [
		'buildings' => [],
	];
	public $jsi18n = [
		'translations' => [],
	];
	
	function __construct(){
		$this->load_dependencies();
		$this->init();
	}
	
	private function load_dependencies(){
		
	}

	private function init(){
		$this->JSData['URLS'] = [
			'PM_BN_URL' => PM_BN_URL,
			'ajax_url' => admin_url( 'admin-ajax.php' ),
		];

		$this->set_js_i18n();

		$this->add_actions();
		$this->add_filters();
	}
	
	private function add_actions(){
		add_action( 'wp_enqueue_scripts', [$this, 'enqueue_scripts'], 98 );
		add_action( 'admin_enqueue_scripts', [$this, 'enqueue_admin_scripts'], 98 );

		add_action( 'wp_print_footer_scripts', [$this, 'set_frontend_footer_script_data'], 1 );
		add_action( 'admin_print_footer_scripts', [$this, 'set_admin_footer_script_data'], 1 );
	}
	
	private function add_filters(){
		
	}
	
	public function enqueue_scripts(){
		global $post, $PmBuildingNavigator;
		
		wp_enqueue_script( 'pmbn-wp', PM_BN_URL . 'assets/js/pmbn-wp.js', ['jquery'], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/js/pmbn-wp.js' ), true );

		//dev
		if($PmBuildingNavigator->settings['dev']){
			wp_enqueue_script( 'pmbn-app-dev-vendors', 'http:///192.168.0.106:8080/pmbn-app-vendors.js', [], $PmBuildingNavigator->settings['version'], true );
			wp_enqueue_script( 'pmbn-app-dev', 'http:///192.168.0.106:8080/pmbn-app.js', [], $PmBuildingNavigator->settings['version'], true );

			wp_enqueue_style( 'pmbn-app-dev-vendors', 'http:///192.168.0.106:8080/pmbn-app-vendors.css', [], $PmBuildingNavigator->settings['version'] );
			wp_enqueue_style( 'pmbn-app-dev', 'http:///192.168.0.106:8080/pmbn-app.css', [], $PmBuildingNavigator->settings['version'] );
		}
		else {
			wp_enqueue_script( 'pmbn-app-vendors', PM_BN_URL . 'assets/js/pmbn-app-vendors.js', [], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/js/pmbn-app-vendors.js' ), true );
			wp_enqueue_script( 'pmbn-app-dev', PM_BN_URL . 'assets/js/pmbn-app.js', ['pmbn-app-vendors'], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/js/pmbn-app.js' ), true );

			wp_enqueue_style( 'pmbn-app-vendors', PM_BN_URL . 'assets/css/pmbn-app-vendors.css', [], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/css/pmbn-app-vendors.css' ) );
			wp_enqueue_style( 'pmbn-app', PM_BN_URL . 'assets/css/pmbn-app.css', ['pmbn-app-vendors'], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/css/pmbn-app.css' ) );
		}

		wp_enqueue_style( 'pmbn-style', PM_BN_URL . 'assets/css/pmbn-style.css', [], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/css/pmbn-style.css' ) );
	}
	
	public function enqueue_admin_scripts(){
		global $post, $PmBuildingNavigator;

		if($post && $post->post_type == 'building-navigators'){
			wp_enqueue_media();

			wp_enqueue_script( 'pmbn-admin', PM_BN_URL . 'assets/js/pmbn-admin.js', ['jquery'], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/js/pmbn-admin.js' ), true );
		
			//dev
			if($PmBuildingNavigator->settings['dev']){
				wp_enqueue_script( 'pmbn-app-dev-vendors', 'http:///192.168.0.106:8080/pmbn-app-vendors.js', [], $PmBuildingNavigator->settings['version'], true );
				wp_enqueue_script( 'pmbn-app-dev', 'http:///192.168.0.106:8080/pmbn-app.js', [], $PmBuildingNavigator->settings['version'], true );
			
				wp_enqueue_style( 'pmbn-app-dev-vendors', 'http:///192.168.0.106:8080/pmbn-app-vendors.css', [], $PmBuildingNavigator->settings['version'] );
				wp_enqueue_style( 'pmbn-app-dev', 'http:///192.168.0.106:8080/pmbn-app.css', [], $PmBuildingNavigator->settings['version'] );
			}
			else {
				wp_enqueue_script( 'pmbn-app-vendors', PM_BN_URL . 'assets/js/pmbn-app-vendors.js', [], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/js/pmbn-app-vendors.js' ), true );
				wp_enqueue_script( 'pmbn-app-dev', PM_BN_URL . 'assets/js/pmbn-app.js', ['pmbn-app-vendors'], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/js/pmbn-app.js' ), true );

				wp_enqueue_style( 'pmbn-app-vendors', PM_BN_URL . 'assets/css/pmbn-app-vendors.css', [], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/css/pmbn-app-vendors.css' ) );
				wp_enqueue_style( 'pmbn-app', PM_BN_URL . 'assets/css/pmbn-app.css', ['pmbn-app-vendors'], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/css/pmbn-app.css' ) );
			}

			wp_enqueue_style( 'pmbn-admin', PM_BN_URL . 'assets/css/pmbn-admin.css', [], $PmBuildingNavigator->settings['version']. filemtime(PM_BN_DIR.'assets/css/pmbn-admin.css' ) );
		}
	}

	public function set_frontend_footer_script_data(){
		$this->JSData['i18n'] = $this->jsi18n;
		wp_localize_script( 'pmbn-wp', 'PMBNData', $this->JSData);
	}

	public function set_admin_footer_script_data(){
		$this->JSData['i18n'] = $this->jsi18n;
		wp_localize_script( 'pmbn-admin', 'PMBNData', $this->JSData);
	}

	public function set_js_i18n(){
		/*
			[
				'default' => '',
				'translation' => __('', PM_BN_TEXTDOMAIN),
				'context' => '',
			]
		
		*/
		$this->jsi18n['data'] = [
			'locale' => get_locale(),
			'langCode' => explode('_', get_locale())[0],
		];
		$this->jsi18n['translations'] = [
			[
				'default' => 'Passive',
				'translation' => __('Passive', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Floor name',
				'translation' => __('Floor name', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Add images',
				'translation' => __('Add images', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Level spacing',
				'translation' => __('Level spacing', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Space above',
				'translation' => __('Space above', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Change SVG',
				'translation' => __('Change SVG', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'select file',
				'translation' => __('select file', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Svg files with groups on the top level representing the floors and groups in the floors representing the apartments.',
				'translation' => __('Svg files with groups on the top level representing the floors and groups in the floors representing the apartments.', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Load SVG',
				'translation' => __('Load SVG', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Only SVG files are allowed.',
				'translation' => __('Only SVG files are allowed.', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Is Apartment',
				'translation' => __('Is Apartment', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Apartment name',
				'translation' => __('Apartment name', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Apartment images',
				'translation' => __('Apartment images', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'General',
				'translation' => _x('General', 'settings', PM_BN_TEXTDOMAIN),
				'context' => 'settings',
			],
			[
				'default' => 'Floor',
				'translation' => __('Floor', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Apartment',
				'translation' => __('Apartment', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Number of rooms',
				'translation' => __('Number of rooms', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Rooms',
				'translation' => __('Rooms', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Living area',
				'translation' => __('Living area', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Garden',
				'translation' => __('Garden', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Terrace',
				'translation' => __('Terrace', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Balcony',
				'translation' => __('Balcony', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Sale price',
				'translation' => __('Sale price', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Currency',
				'translation' => __('Currency', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Period',
				'translation' => __('Period', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Overheads',
				'translation' => __('Overheads', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Available from',
				'translation' => __('Available from', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Status',
				'translation' => __('Status', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Registration URL',
				'translation' => __('Registration URL', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Fact sheet',
				'translation' => __('Fact sheet', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Add file',
				'translation' => __('Add file', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Graphic',
				'translation' => __('Graphic', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Apartments',
				'translation' => __('Apartments', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Rent price period',
				'translation' => __('Rent price period', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Clipboard data copied into apartment.',
				'translation' => __('Clipboard data copied into apartment.', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Apartment copied on clipboard.',
				'translation' => __('Apartment copied on clipboard.', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Copy',
				'translation' => __('Copy', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Paste',
				'translation' => __('Paste', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Display in table column',
				'translation' => __('Display in table column', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Floor index',
				'translation' => __('Floor index', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Online Registration',
				'translation' => __('Online Registration', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Search',
				'translation' => __('Search', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'to',
				'translation' => _x('to', 'date range', PM_BN_TEXTDOMAIN),
				'context' => 'date range',
			],
			[
				'default' => 'Availability',
				'translation' => __('Availability', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Available',
				'translation' => __('Available', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Sold',
				'translation' => __('Sold', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Rented',
				'translation' => __('Rented', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Main color',
				'translation' => __('Main color', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Outdoor types',
				'translation' => __('Outdoor types', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Blueprints',
				'translation' => __('Blueprints', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Sold status row opacity',
				'translation' => __('Sold status row opacity', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Previous Floor',
				'translation' => __('Previous Floor', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Next Floor',
				'translation' => __('Next Floor', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Preview',
				'translation' => __('Preview', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'On',
				'translation' => __('On', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Off',
				'translation' => __('Off', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Rent price',
				'translation' => __('Rent price', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Copy Apartment Data',
				'translation' => __('Copy Apartment Data', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Paste Apartment Data',
				'translation' => __('Paste Apartment Data', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Clipboard data pasted into apartment.',
				'translation' => __('Clipboard data pasted into apartment.', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Select',
				'translation' => __('Select', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Close',
				'translation' => __('Close', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Apartments not found',
				'translation' => __('Apartments not found', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Floor plan',
				'translation' => __('Floor plan', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Basement',
				'translation' => __('Basement', PM_BN_TEXTDOMAIN),
			],
			[
				'default' => 'Net rent',
				'translation' => __('Net rent', PM_BN_TEXTDOMAIN),
			],
		];
	}
}