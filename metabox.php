<?php 

	/**

	 * Plugin Name:			Metabox
	 * Plugin URI:			http://journeybyweb.com/
	 * Description:			Decorate your site easily by creating metabox.
	 * Version:				1.0.0
	 * Requires at least:	5.2
	 * Requires PHP:		7.2
	 * Author:				Abdul Hay
	 * Author URI:			http://abdulhay.journeybyweb.com/
	 * License:				GPL v2 or later
	 * License URI:			https://www.gnu.org/licenses/gpl-2.0.html
	 * Text Domain:			MetaBox
	 * Domain Path:			/languages

	*/

	/*function MetaBox_activation_hook(){}
		register_activation_hook( __FILE__, 'MetaBox_activation_hook' );

	function MetaBox_deactivation_hook(){}
		register_deactivation_hook( __FILE__, 'MetaBox_activation_hook' );*/


	class Metabox{
		public function __construct(){
			add_action('plugins_loaded', array($this, 'MetaBox_load_textdomain'));
			add_action( 'admin_menu', array($this, 'MetaBox_add_new_Mbox'));
			add_action( 'save_post', array($this, 'MetaBox_save_Value'));
			add_action( 'admin_enqueue_scripts', array($this, 'MetaBox_admin_assets') );
			add_action( 'public_en', array($this, 'MetaBox_admin_assets') );
		}

		function MetaBox_admin_assets(){
			wp_enqueue_style('MetaBox_admin_style', plugin_dir_url(__FILE__).'/admin/css/style.css', null, time() );
			wp_enqueue_style('jquery_ui_style', '//cdnjs.cloudflare.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.css', null, time() );
			wp_enqueue_script('MetaBox_js', plugin_dir_url( __FILE__ ).'/admin/js/meta_main.js', array('jquery','jquery-ui-datepicker'), time(), true);
		}
		private function is_secured($nonce_field, $action, $post_id){

			$nonce = isset($_POST[$nonce_field]) ? $_POST[$nonce_field] : '';
			if ($nonce == '') {
				return false;
			}
			if (!wp_verify_nonce($nonce, $action)) {
				return false; 
			}
			if (!current_user_can('edit_post', $post_id)) {
				return false;
			}
			if (wp_is_post_autosave($post_id)) {
				return false;
			}
			if (wp_is_post_revision($post_id)) {
				return false;
			}
			return true;
		}

		Function MetaBox_save_Value($post_id){
			if (!$this -> is_secured('MetaBox_location_field', 'MetaBox_location', $post_id)) {
				return $post_id;
			}

			$location = isset($_POST['MetaBox_location']) ? $_POST['MetaBox_location'] : '';
			$country = isset($_POST['MetaBox_country']) ? $_POST['MetaBox_country'] : '';
			$checkBox = isset($_POST['MetaBox_checkBox']) ? $_POST['MetaBox_checkBox'] : 0;
			$colors = isset($_POST['MetaBox_color1']) ? $_POST['MetaBox_color1'] : array();
			$color = isset($_POST['MetaBox_color2']) ? $_POST['MetaBox_color2'] : array();
			$select_color = isset($_POST['MetaBox_select_color']) ? $_POST['MetaBox_select_color'] : array();

			// if ($location=='' || $country==''){
			// 	return $post_id;
			// }
			$location = sanitize_text_field($location);
			$country = sanitize_text_field($country);

			update_post_meta( $post_id, 'MetaBox_location', $location);
			update_post_meta( $post_id, 'MetaBox_country', $country);
			update_post_meta( $post_id, 'MetaBox_checkBox', $checkBox);
			update_post_meta( $post_id, 'MetaBox_color1', $colors);
			update_post_meta( $post_id, 'MetaBox_color2', $color);
			update_post_meta( $post_id, 'MetaBox_select_color', $select_color);
		}

		function MetaBox_add_new_Mbox(){
			add_meta_box( 
				'MetaBox_post_location', 
				__('Location Info', 'MetaBox'),
				array($this, 'MetaBox_display_MetaBoxes'),
				array('post','page')
			);

			add_meta_box( 
				'MetaBox_Book_info',
				__('Book Info', 'MetaBox'),
				array($this, 'MetaBox_display_Book_info'),
				array('book')
			);

			add_meta_box( 
				'MetaBox_Image_info',
				__('Image Info', 'MetaBox'),
				array($this, 'MetaBox_display_Image_info'),
				array('post')
			);
		}

		function MetaBox_display_Image_info(){

			$metabox_html = <<<EOD
				<div class="fields">
					<div class="field_c">
						<div class="label_c" for="MetaBox_image">Upload Images</div>
						<button id="MetaBox_image" class="image_btn">Select File</button>
						<input type="hidden" id="MetaBox_img_id">
						<input type="hidden" id="MetaBox_img_url">
					</div>
				</div>
				<div class="clear_fixed"></div>
			EOD;
			echo $metabox_html;

		}

		function MetaBox_display_Book_info(){

			wp_nonce_field( 'MetaBox_Books', 'MetaBox_Books_nonce');

			$metabox_html = <<<EOD
				<div class="fields">
					<div class="field_c">
						<div class="label_c" for="book_author">Book Author</div>
						<div class="input_c">
							<input type="text" id="book_author"class="widefat">
						</div>
					</div>
					<div class="field_c">
						<div class="label_c" for="book_isbn">Book ISBN</div>
						<div class="input_c">
							<input type="text" id="book_isbn">
						</div>
					</div>
					<div class="field_c">
						<div class="label_c" for="publication_date">Publication Date</div>
						<div class="input_c">
							<input type="text" id="publication_date" class="MetaBox_dateUI">
						</div>
					</div>
				</div>
				<div class="clear_fixed"></div>
			EOD;
			echo $metabox_html;
		}



		function MetaBox_display_MetaBoxes($post){
			$location 	= get_post_meta( $post -> ID, 'MetaBox_location', true );
			$country 	= get_post_meta( $post -> ID, 'MetaBox_country', true );
			$checkBox 	= get_post_meta( $post -> ID, 'MetaBox_checkBox', true );
			$checked 	= $checkBox==1?'checked':'';
			$save_colors = get_post_meta( $post -> ID, 'MetaBox_color1', true);
			$save_color = get_post_meta( $post -> ID, 'MetaBox_color2', true);

			$label1 	= __('Location', 'MetaBox');
			$label2 	= __('Country', 'MetaBox');
			$label3 	= __('CheckBox', 'MetaBox');
			$label4 	= __('Colors Box', 'MetaBox');
			$label5 	= __('Colors Radio', 'MetaBox');
			$label6 	= __('Select Color', 'MetaBox');

			$colors 	= array('Red', 'Green', 'Pink', 'Yellow', 'Blue','Orrange');

			wp_nonce_field( 'MetaBox_location', 'MetaBox_location_field' );

			$metabox_html	= <<<EOD
				<p>
					<label for="MetaBox_location" class='info_label_c'>{$label1}:</label>
					<input type="text" name="MetaBox_location" id="MetaBox_location" value="{$location}"/> 
					<br/>
					<label for="MetaBox_country" class='info_label_c'>{$label2}:</label>
					<input type="text" name="MetaBox_country" id="MetaBox_country" value="{$country}"/>
				</p>
				<p>
					<label for="MetaBox_checkBox" class='info_label_c'>{$label3}:</label>
					<input type="checkbox" name="MetaBox_checkBox" id="MetaBox_checkBox" value="1" {$checked}/> আপনি কি ইচ্ছুক?
				</p>
				<p>
					<label class='info_label_c'>{$label4}:</label>
				
			EOD;

			foreach ($colors as $color) {
				$checked = in_array($color, $save_colors) ? 'checked' : '';
				$metabox_html	.= <<<EOD
					<input type="checkbox" name="MetaBox_color1[]" id="MetaBox_color1{$color}" value="{$color}" {$checked}/>
					<label for="MetaBox_color1{$color}">{$color}</label>
				EOD;
			}
					
			$metabox_html.="</p>";

			$metabox_html	.= <<<EOD
				<p>
					<label class='info_label_c'>{$label5}:</label>
			EOD;

			foreach ($colors as $color) {
				$checked =( $color == $save_color  ) ? "checked='checked'" : '';
				$metabox_html	.= <<<EOD
					<input type="radio" name="MetaBox_color2" id="MetaBox_color2{$color}" value="{$color}" {$checked}/>
					<label for="MetaBox_color2{$color}">{$color}</label>
				EOD;
			}
					
			$metabox_html.="</p>";

			// Dropdown select clolor start

			$select_color = get_post_meta($post-> ID, 'MetaBox_select_color', true);

			$dropdown_html = "<option value='0'>".__('Select a color', 'MetaBox')."</option>";

			foreach ($colors as $color) {
				$selected = '';
				if ($color == $select_color) {
					$selected = 'selected';				
				}
				$dropdown_html .= sprintf("<option %s value='%s'> %s </option>", $selected, $color, $color);
			}

			$metabox_html.=<<<EOD
				<p>
					<label for="MetaBox_select_color" class='info_label_c'>{$label6}:</label>
					<select name="MetaBox_select_color" id="MetaBox_select_color">
						{$dropdown_html}
					</select>
				</p>
			EOD;

			echo $metabox_html;		
		}


		public function MetaBox_load_textdomain(){
			load_plugin_textdomain('MetaBox', false, dirname(__FILE__).'/languages');
		}
	}
	new Metabox();

?>