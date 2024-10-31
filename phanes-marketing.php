<?php
/*
Plugin Name: Phanes Marketing
Plugin URI: https://phanes.co
Description: Phanes Marketing is a WooCommerce Marketing Tool that displays Youtube Reviews on product pages to increase sales conversion.
Author: Phanes
Version: 1.0.1
Author URI: https://phanes.co/
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'Phanes_Marketing' ) ) :

class Phanes_Marketing {

	public $slug;

	public $url;

	public $path;

	public $basename;

	public $version = '1.0.0';

	private static $instance = null;

	public static function get_instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
			self::$instance->init();
		}
		return self::$instance;
	}

	private function init() {
		$this->include_files();

		$this->url = plugin_dir_url( __FILE__ );
		$this->path = plugin_dir_path( __FILE__ );
		$this->basename = plugin_basename( __FILE__ );
		$this->slug = dirname( $this->basename );
		$this->license_validator = new Phanes_License_Validator();
		$this->has_valid_license = $this->license_validator->is_valid();

		$this->include_free_files();
		$this->add_hook();
	}

	public function add_hook() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_menu', array( $this, 'register_fields' ) );
		add_filter( 'plugin_action_links_' . $this->basename, array( $this, 'add_settings_link' ) );

		add_action( 'wp_footer', array( $this, 'add_tracking_code' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
	}

	public function add_tracking_code() {
		$options = get_option( 'phanes_marketing', array() );

		if ( isset( $options['ga_tracking_code'] ) ) {
			echo $this->sanitize_tracking_code( $options['ga_tracking_code'] );
		}
	}

	public function include_files() {
		include plugin_dir_path( __FILE__ ) . 'class-license-validator.php';
	}

	public function include_free_files() {
		if ( $this->has_valid_license ) {
			include plugin_dir_path( __FILE__ ) . 'class-youtube.php';
		}
	}

	public function enqueue_scripts() {
		if ( ! $this->has_valid_license ) {
			return;
		}
		wp_enqueue_style(
			'jquery-magnific-popup',
			$this->url . 'assets/css/magnific-popup.css',
			null,
			$this->version
		);
		wp_enqueue_style(
			'jquery-owl.carousel',
			$this->url . 'assets/css/owl.carousel.css',
			null,
			$this->version
		);
		wp_enqueue_style(
			'phanes-style',
			$this->url . 'assets/css/phanes-style.css',
			null,
			$this->version
		);

		wp_enqueue_script(
			'jquery-owl.carousel',
			$this->url . 'assets/js/owl.carousel.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		wp_enqueue_script(
			'jquery-magnific-popup',
			$this->url . 'assets/js/jquery.magnific-popup.min.js',
			array( 'jquery' ),
			$this->version,
			true
		);
		wp_enqueue_script(
			'jquery-magnific-popup-init',
			$this->url . 'assets/js/magnific-popup-init.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	public function add_menu() {
		add_menu_page(
			'Phanes Marketing Settings',
			'Phanes Marketing',
			'manage_options',
			$this->slug,
			array( $this, 'render_settings_page' ),
			'dashicons-megaphone'
		);

		add_submenu_page(
			$this->slug,
			'Phanes Marketing License',
			'License',
			'manage_options',
			$this->license_validator->slug,
			array( $this->license_validator, 'render_page' )
		);
	}

	public function sanitize_settings_data( $input ) {
		$output = array();

		if ( isset( $input['ga_tracking_code'] ) ) {
			$output['ga_tracking_code'] = $this->sanitize_tracking_code( $input['ga_tracking_code'] );
		}

		if ( isset( $input['enable_youtube'] ) && $this->has_valid_license ) {
			$output['enable_youtube'] = 1;
		} else {
			$input['enable_youtube'] = 0;
		}

		if ( isset( $input['youtube_apikey'] ) ) {
			$output['youtube_apikey'] = sanitize_text_field( $input['youtube_apikey'] );
		}

		return $output;
	}

	public function sanitize_tracking_code( $str ) {
		return wp_kses( $str, array(
			'script' => array()
		) );
	}

	public function register_fields() {
		register_setting(
			$this->slug,
			'phanes_marketing',
			array( $this, 'sanitize_settings_data' )
		);

		add_settings_section(
			'pm_google_analytic',
			'Google Analytics',
			array( $this, 'render_section' ),
			$this->slug
		);

		add_settings_section(
			'pm_youtube',
			'WooCommerce Youtube Reviews',
			array( $this, 'render_section' ),
			$this->slug
		);

		add_settings_field(
			'ga_tracking_code',
			'Tracking Code',
			array( $this, 'render_textarea_field' ),
			$this->slug,
			'pm_google_analytic'
		);

		add_settings_field(
			'enable_youtube',
			'Enable Youtube',
			array( $this, 'render_checkbox_field' ),
			$this->slug,
			'pm_youtube'
		);

		add_settings_field(
			'youtube_apikey',
			'Youtube API Key',
			array( $this, 'render_text_field' ),
			$this->slug,
			'pm_youtube'
		);
	}

	public function render_checkbox_field() {
		printf(
			'<input type="checkbox" id="enable_youtube" name="phanes_marketing[enable_youtube]" value="1" %s %s /> %s',
			checked( 1, isset( $this->settings['enable_youtube'] ) ? $this->settings['enable_youtube'] : 0, false ),
			( ! $this->has_valid_license  ? 'disabled' : '' ),
			( ! $this->has_valid_license ? '<p class="description">This feature requires your license code to be fully activated. Please visit <a href="https://www.phanes.co">phanes.co</a> and buy your license today!' : '' )
		);
	}

	public function render_text_field() {
		printf(
			'<input type="text" id="youtube_apikey" class="regular-text" name="phanes_marketing[youtube_apikey]" value="%s" placeholder="Add youtube api key here" />',
			isset( $this->settings['youtube_apikey'] ) ? esc_attr( $this->settings['youtube_apikey'] ) : ''
		);
	}

	public function render_textarea_field() {
		printf(
			'<textarea rows="10" type="text" id="ga_tracking_code" class="regular-text" name="phanes_marketing[ga_tracking_code]" placeholder="Add google analytics tracking code here">%s</textarea>',
			isset( $this->settings['ga_tracking_code'] ) ? $this->sanitize_tracking_code( $this->settings['ga_tracking_code'] ) : ''
		);
	}

	public function render_section() {
		?>
		<p class="description">Phanes Marketing is a merchant marketing tool that simplifies marketing management for a merchant's woocommerce store.</p>
		<?php
	}

	public function render_settings_page() {
		$this->settings = get_option( 'phanes_marketing', array() );
		include $this->path . 'admin/settings-page.php';
	}

	public function add_settings_link( $links ) {
		$link = sprintf( '<a href="%s">%s</a>',
			add_query_arg( 'page', $this->slug, admin_url( 'admin.php' ) ),
			'Settings'
			);
		array_unshift( $links, $link );
		return $links;
	}
}

function PM_Instance() {
	return Phanes_Marketing::get_instance();
}

PM_Instance();

endif;