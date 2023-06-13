<?php
/**
 * Building Navigator Editor functions
 *
 * @package WordPress
 * @since PmBN 1.0.0
*/

// exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;

class PmBNNavigator{
	
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
		/* FONTEND */
		add_action( 'init', [$this, 'add_shortcode'] );

		/*ADMIN*/
		add_action( 'edit_form_after_editor', [$this, 'render_editor'] );
		add_action( 'post_edit_form_tag', [$this, 'post_form_novaidate'], 10, 1 );

		//validate
		add_action( 'wp_ajax_pmbn_validate_building_config', [$this, 'pmbn_validate_building_config'] );

		//save
		add_action( 'save_post_building-navigators', [$this, 'save_building_config'] );
	}
	
	private function add_filters(){
	}

	public function post_form_novaidate($post){
		if($post->post_type == 'building-navigators'){
			echo ' novalidate="novalidate" ';
		}
	}

	public function add_shortcode(){
		add_shortcode( 'building-navigator', [$this, 'render_shortcode'] );
	}

	public function render_shortcode($atts){
		$post = (isset($atts['id']))? get_post($atts['id']) : false;

		if(!$post || $post->post_type !== 'building-navigators'){
			return __('Building navigator not found...', PM_BN_TEXTDOMAIN);
		}

		ob_start();
		global $PmBuildingNavigator;

		$config = $this->get_config($post->ID);

		$config['mode'] = 'view';
		$config['editor'] = [
			'activeTab' => 'general',
			'activeFloor' => 0,
			'selectedGroup' => null,
		];

		$PmBuildingNavigator->PmBNScripts->JSData['buildings'][] = [
			'id' => $post->ID,
			'config' => ($config)? $config : [],
		];
		
		?>
			<div class="pmbn-app" data-id="<?= $post->ID ?>"></div>
		<?php
		return ob_get_clean();
	}

	public function get_image_sizes($attachment_id){
		$meta_data = wp_get_attachment_metadata( $attachment_id);
		$sizes = [];
		foreach ( $meta_data['sizes'] as $size_name => $size_data ) {
			$src = wp_get_attachment_image_src( $attachment_id, $size_name )[0];
			$sizes[$size_name] = [
			    'url' => $src,
			    'width' => $size_data['width'],
			    'height' => $size_data['height'],
			    'orientation' => $size_data['height'] > $size_data['width'] ? 'portrait' : 'landscape'
			];
		}

		$full_src = wp_get_attachment_image_src( $attachment_id, 'full' )[0];
		$sizes['full'] = [
		    'url' => $full_src,
		    'width' => $meta_data['width'],
		    'height' => $meta_data['height'],
		    'orientation' => $meta_data['height'] > $meta_data['width'] ? 'portrait' : 'landscape'
		];
		return $sizes;
	}

	public function get_config($post_id){
		$config = get_post_meta($post_id, 'pmbn_building_config', true);

		if($config){
			//validate images
			if(isset($config['building']) && isset($config['building']['floors'])){
				foreach($config['building']['floors'] as $fi => $floor){
					if(isset($floor['groups'])){
						foreach($floor['groups'] as $gi => $group){
							if(isset($group['images'])){
								$valid_images = [];

								foreach($group['images'] as $ii => $image){
									if(isset($image['id'])){
										$img_post = get_post($image['id']);

										if($img_post && $img_post->post_type == 'attachment'){
											$meta_data = wp_get_attachment_metadata( $img_post->ID);
											$valid_images[] = [
												'id' => $img_post->ID,
												'caption' => wp_get_attachment_caption($img_post->ID),
												'alt' => get_post_meta($img_post->ID, '_wp_attachment_image_alt', true),
												'name' => $img_post->post_title,
												'filename' => basename( $meta_data['file'] ),
												'url' => wp_get_attachment_url( $img_post->ID ),
												'sizes' => $this->get_image_sizes($img_post->ID),
												'mime' => $img_post->post_mime_type,
												'width' => $meta_data['width'],
												'height' => $meta_data['height'],
											];
										}
									}
								}

								$group['images'] = $valid_images;
							}

							$floor['groups'][$gi] = $group;
						}
					}

					$config['building']['floors'][$fi] = $floor;
				}
			}
		}

		return (!empty($config))? $config : [];
	}
	
	public function render_editor($post){
		if($post->post_type !== 'building-navigators'){
			return;
		}

		global $PmBuildingNavigator;

		$config = $this->get_config($post->ID);

		$config['mode'] = 'editor';

		$PmBuildingNavigator->PmBNScripts->JSData['buildings'][] = [
			'id' => $post->ID,
			'config' => ($config)? $config : [],
		];

		?>
			<div class="pmbn-app-shortcode-wrapper" ><?= __('Shortcode', PM_BN_TEXTDOMAIN) ?>: <span>[building-navigator id="<?= $post->ID ?>"]</span></div>
			<input type="hidden" name="pmbn-appconfigs[<?= $post->ID ?>]" />
			<div class="pmbn-app" data-id="<?= $post->ID ?>"></div>
		<?php
	}

	public function pmbn_validate_building_config(){
		$errors = [];

		//

		wp_send_json([
			'errors' => $errors,
		]);
		wp_die();
	}

	public function save_building_config(){
		if(isset($_REQUEST['pmbn-appconfigs'])){
			foreach($_REQUEST['pmbn-appconfigs'] as $post_id => $config_json){
				if(current_user_can('edit_post', $post_id)){
					$config_json = stripslashes($config_json);

					if(json_decode($config_json) && json_last_error() === JSON_ERROR_NONE){
						$config = json_decode($config_json, true);
						update_post_meta($post_id, 'pmbn_building_config', $config);
					}
					else {
						wp_die(__('Oops! An unexpected error occurred while saving the building navigator. Please try again.', PM_BN_TEXTDOMAIN));
					}
				}
			}
		}
	}
}