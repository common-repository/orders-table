<?php
class ot_post_type {
	public $options;
	
	function __construct() {
		$this->options = get_option('ot_main_options');
		
		add_action('init', array($this, 'post_type_register'));
		add_filter('post_updated_messages', array($this, 'edit_post_messages'));
	}
	
	public function post_type_supports() {
		$supports = array();
		$supports[0] = 'title';
		
		if (isset($this->options) && !empty($this->options)) {
			if (isset($this->options['order-description']) && $this->options['order-description'] == 1) {
				$description = 'editor';
				array_push($supports, $description);
			}
			if (isset($this->options['order-author']) && $this->options['order-author'] == 1) {
				$author = 'author';
				array_push($supports, $author);
			}
		}

		return $supports;
	}
	
	public function post_type_register() {
		$labels = array(
			'name' =>  __('Orders Table', 'orders-table'),
			'singular_name' => __('Description order', 'orders-table'),
			'add_new' => __('Add order', 'orders-table'),
			'add_new_item' => __('Add new order', 'orders-table'),
			'edit_item' => __('Edit order', 'orders-table'),
			'new_item' => __('New  order', 'orders-table'),
			'all_items' => __('All orders', 'orders-table'),
			'not_found' =>  __('Orders not found.', 'orders-table'),
			'not_found_in_trash' => __('The basket does not have the orders.', 'orders-table'),
			'menu_name' => __('Orders Table', 'orders-table')	
		);
		$args = array(
			'labels' => $labels,
			'public' => false,
			'show_ui' => true,
			'menu_icon' => 'dashicons-list-view',
			'menu_position' => 3,
			'supports' => $this->post_type_supports()
		);
		register_post_type('orders-table', $args);
		
		if (isset($this->options) && !empty($this->options)) {
			if (isset($this->options['order-category']) && $this->options['order-category'] == 1) {
				register_taxonomy( 'orders-category', 'orders-table', 
					array( 
						'hierarchical' => true, 
						'label' => __('Category', 'orders-table') 
					)
				);
			}
		}
	}
	
	public function edit_post_messages($messages) {
		global $post, $post_ID;
		
		$messages['orders-table'] = array( 
			0 => '',
			1 => sprintf(__('Order updated.', 'orders-table')),
			2 => __('The parameter is updated.', 'orders-table'),
			3 => __('The parameter is remove.', 'orders-table'),
			4 => __('Order is updated', 'orders-table'),
			5 => isset($_GET['revision'])?sprintf(__('Order restored from the editorial: %s', 'orders-table'), wp_post_revision_title((int)$_GET['revision'], false)):false,
			6 => sprintf(__('Order  published on the website.', 'orders-table')),
			7 => __('Order saved.', 'orders-table'),
			8 => sprintf(__('Order submitted for review.', 'orders-table')),
			9 => sprintf(__('Scheduled for publication: <strong>%1$s</strong>.', 'orders-table'), date_i18n(__('M j, Y @ G:i'), strtotime($post->post_date)), esc_url(get_permalink($post_ID))),
			10 => sprintf(__('Draft updated order.', 'orders-table')),
		);
	 
		return $messages;
	}
}

class ot_new_post_status {
	function __construct() {
		$options = get_option('ot_main_options');
		
		if (isset($options['order-status']) && $options['order-status'] == 1) {
			add_action('init', array($this, 'new_post_status'));
			add_action('admin_footer-post-new.php', array($this, 'new_post_status_list')); 
			add_action('admin_footer-post.php', array($this, 'new_post_status_list')); 
			add_filter('display_post_states', array($this, 'new_post_status_display'));
		}
	}
	
	public function new_post_status() { 
		register_post_status('av', 
			array(
				'label' =>  __('Awaiting verification', 'orders-table'),
				'label_count' => _n_noop( 
					__('Awaiting verification', 'orders-table') . '<span class="count">(%s)</span>',  
					__('Awaiting verification', 'orders-table') . '<span class="count">(%s)</span>'
				),
				'public' => true,
				'exclude_from_search' => false,
				'show_in_admin_all_list' => true,
				'show_in_admin_status_list' => true 
			) 
		);
		
	}
	
	public function new_post_status_list() {
		global $post;

		if ($post->post_type == 'orders-table') { 
			?>
			<script> 
				jQuery(function($){
					
					<?php if ($post->post_status == 'av') { ?>
					$("#post-status-display").text("<?php echo __('Awaiting verification', 'orders-table') ?>");
					<?php } ?>
					
					$("select#post_status").append("<option value=\"av\" <?php selected($post->post_status, 'av') ?>><?php echo __('Awaiting verification', 'orders-table') ?></option>");
				});
			</script>';
			<?php
		}
	}
	
	public function new_post_status_display($statuses) {
		global $post;
		
		if ($post->post_type == 'orders-table') { 
			if ($post->post_status == 'av'){ 
				return array(__('Awaiting verification', 'orders-table'));
			}
		}
		
		return $statuses;
	}	
}

class ot_option_page {
	public $options;
	
	function __construct() {
		$this->options = get_option('ot_main_options');
		
		add_action('admin_menu', array($this, 'add_plugin_page'));
		add_action('admin_init', array($this, 'plugin_settings'));
	}
	
	public function add_plugin_page() {
		add_options_page(__('Orders table options', 'orders-table'), 'Orders Table', 'manage_options', 'ot_options', array($this, 'options_page_html'));
	}	
	
	public function options_page_html() {
		?>
		<div class="wrap">
			<?php require_once('options-page.php'); ?>
		</div>
		<?php
	}
	
	public function plugin_settings() {
		register_setting('ot_option_group', 'ot_main_options', array($this, 'sanitize_callback'));
	}

	public function get_extra_html($i = 1) {
		$field_name = @ $this->options['extra']['extra-field-' . $i]['field-name']?:null;
		$field_type = @ $this->options['extra']['extra-field-' . $i]['field-type']?:null;
		$field_front = @ $this->options['extra']['extra-field-' . $i]['field-front']?:null;
		$field_required = @ $this->options['extra']['extra-field-' . $i]['field-required']?:null;
		?>
		<fieldset class="extra-field-<?php echo $i ?>">
			<legend><?php echo __('Extra Field', 'orders-table'); ?> <?php echo $i ?></legend>
			<div class="row">
				<div class="col name">
					<label><span><?php echo __('Field name:', 'orders-table'); ?></span>
						<input type="text" name="ot_main_options[extra][extra-field-<?php echo $i ?>][field-name]" value="<?php echo $field_name ?>">
					</label>
				</div>		
				<div class="col type">
					<label><span><?php echo __('Field type:', 'orders-table'); ?></span>
						<select name="ot_main_options[extra][extra-field-<?php echo $i ?>][field-type]">
							<option value=""><?php echo __('Not selected', 'orders-table'); ?></option>
							<option value="text" <?php selected($field_type, 'text') ?>><?php echo __('Text', 'orders-table'); ?></option>
							<option value="number" <?php selected($field_type, 'number') ?>><?php echo __('Number', 'orders-table'); ?></option>
							<option value="date" <?php selected($field_type, 'date') ?>><?php echo __('Data', 'orders-table'); ?></option>
							<option value="email" <?php selected($field_type, 'email') ?>><?php echo __('E-mail', 'orders-table'); ?></option>
							<option value="checkbox" <?php selected($field_type, 'checkbox') ?>><?php echo __('Checkbox', 'orders-table'); ?></option>
							<option value="range" <?php selected($field_type, 'range') ?>><?php echo __('Range', 'orders-table'); ?></option>
						</select>
					</label>
				</div>		
				<div class="col front">
					<label><span><?php echo __('Field front:', 'orders-table'); ?></span>
						<input type="text" name="ot_main_options[extra][extra-field-<?php echo $i ?>][field-front]" value="<?php echo $field_front ?>">
					</label class="">
				</div>		
				<div class="col required">
					<span><?php echo __('Required?', 'orders-table'); ?></span>
					<label>
						<input type="checkbox" name="ot_main_options[extra][extra-field-<?php echo $i ?>][field-required]" value="1" <?php echo checked(1, $field_required, 0) ?>>
						<span><?php echo __('on/off', 'orders-table'); ?></span>
					</label>
				</div>
				<div class="dashicons dashicons-no-alt delete-field"></div>
				<div class="dashicons dashicons-move move-field"></div>
			</div>
		</fieldset>	
		<?php
	}
	
	public function sanitize_callback($options) {
		if (isset($options['extra']) && count($options['extra']) == 1) {
			if (empty($options['extra']['extra-field-1']['field-name'])) {
				unset($options['extra']);
			}
		}
		
		return $options;
	}
}

class ot_meta_box {
	public $options;
	
	function __construct() {
		$this->options = get_option('ot_main_options');
		
		add_action('add_meta_boxes', array($this, 'my_extra_fields'), 1);
		add_action('save_post', array($this, 'my_extra_fields_update'), 0);
	}
	
	public function my_extra_fields() {
		if (isset($this->options['extra']) && count($this->options['extra']) > 0) {
			for ($i = 1; $i <= count($this->options['extra']); $i++) {
				$name = $this->options['extra']['extra-field-' . $i]['field-name'];
				add_meta_box('extra-field-'.$i, $name, array($this, 'extra_fields_box_func'), 'orders-table', 'normal', 'high');
			}
		}
	}
	
	public function extra_fields_box_func($post, $data) {
		$i = intval(preg_replace('/[^0-9]+/', '', $data['id']), 10);
		$type = $this->options['extra']['extra-field-' . $i]['field-type'];
		$front = $this->options['extra']['extra-field-' . $i]['field-front'];
		$required = $this->options['extra']['extra-field-' . $i]['field-required'] == 1?'required':null;
		?>
		<input type="hidden" name="extra_fields_nonce" value="<?php echo wp_create_nonce(__FILE__); ?>" />
		<p><input type="<?php echo $type ?>" placeholder="<?php echo $front ?>" name="extra[extra-filed-<?php echo $i ?>]" value="<?php echo get_post_meta($post->ID, 'extra-filed-' . $i, 1) ?>" style="width:100%" <?php echo $required ?>/></p>
		<?php
	}

	public function my_extra_fields_update($post_id){
		if (empty( $_POST['extra']) || !wp_verify_nonce($_POST['extra_fields_nonce'], __FILE__) || wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) {
			return false;
		} else {
			$_POST['extra'] = array_map('sanitize_text_field', $_POST['extra']);
			foreach ($_POST['extra'] as $key => $value) {
				if (empty($value)) {
					delete_post_meta($post_id, $key);
				}
				update_post_meta($post_id, $key, $value);
			}
			
			return $post_id;
		}
	}
}

class ot_add_new {
	public $options;
	public $data;
	
	function __construct($post) {
		$this->options = get_option('ot_main_options');
		$this->data = $post;
		
		$this->first_step();
	}
	
	public function first_step() {
		$order = array(
			'post_type' => 'orders-table',
			'post_title' => $this->data['title'],
			'post_status' => 'publish'
		);

		if (isset($this->options) && !empty($this->options)) {
			if (isset($this->options['order-status']) && $this->options['order-status'] == 1) {
				$order['post_status'] = 'av';
			}
			if (isset($this->options['order-description']) && $this->options['order-description'] == 1) {
				$order['post_content'] = $this->data['content'];
			}
			if (isset($this->options['order-author']) && $this->options['order-author'] == 1) {
				$order['post_author'] = !wp_get_current_user()->ID == 0?wp_get_current_user()->ID:'';
			}
		}
		
		$this->second_step($order);
	}
	
	public function second_step($args) {
		$order = wp_insert_post($args);
		
		$this->third_step($order);
	}
	
	public function third_step($id) {
		if (isset($this->options) && !empty($this->options)) {
			if (isset($this->options['order-category']) && $this->options['order-category'] == 1) {
				$ot_term = get_term($this->data['orders-category'], 'orders-category');
				$name_category = $ot_term->slug;
				wp_set_object_terms($id, $name_category, 'orders-category');
			}
			if (isset($this->data['extra'])) {
				for ($i = 1; $i <= count($this->data['extra']); $i++) {
					$val = $this->data['extra']['extra-filed-' . $i];
					$key = array_search($val, $this->data['extra']);
					update_post_meta($id, $key, $val);
				}
			}
		}
	}
}

class ot_includes {
	function __construct() {
		add_action('wp_enqueue_scripts', array($this, 'add_styles'));
		add_action('admin_enqueue_scripts', array($this, 'my_enqueue_stuff'));
		add_action('plugins_loaded', array($this,'lang_load'));
	}
	
	public function add_styles() {
		wp_enqueue_style('ot-style', plugin_dir_url(dirname(__FILE__)). 'add/css/ot-style.css'); 
	}	
	
	public function my_enqueue_stuff($page) {
		if ($page == 'settings_page_ot_options') {
			wp_enqueue_style('ot-style-admin', plugin_dir_url(dirname(__FILE__)). 'add/css/ot-style-admin.css');
			wp_enqueue_script('jquery-ui-sortable');			
			wp_enqueue_script('ot-admin-script', plugin_dir_url(dirname(__FILE__)). 'add/js/ot-admin-script.js', array('jquery'));
			wp_localize_script( 'ot-admin-script', 'change_text', array( 
				'enable' => __('On', 'orders-table'), 
				'disable' => __('Off', 'orders-table') 
			));
		}	
	}
	
	public function lang_load() {
		load_plugin_textdomain('orders-table', false, dirname(plugin_basename( __FILE__ )) . '/lang/'); 
	}	
}

class ot_dislpay_front {
	function __construct() {
		add_shortcode('orders-table', array($this, 'shortcode_show_table'));
	}
	
		
	public function shortcode_show_table() {
		ob_start();
		require_once('frontend.php'); 
		$content = ob_get_clean();
		
		return $content;
	}
}