<?php
/**
 * License validator
 *
 * @package  Phanes Marketing
 * @author   Phanes
 */


if ( ! class_exists( 'Phanes_License_Validator' ) ) :

class Phanes_License_Validator {

	public $response = array();

	/**
	 * Store all notifications
	 * @var string
	 */
	protected static $_notices = array();

	protected $api_url = 'https://phanes.co/';

	public function __construct() {
		$this->slug = PM_Instance()->slug . '-license';

		add_action( 'admin_init', array( $this, 'init' ) );
		add_action( 'admin_menu', array( $this, 'register_fields' ) );
		add_action( 'admin_notices', array( $this, 'display_notices' ), 999 );
	}

	/**
	 * Show license notification
	 * @return void
	 */
	public function init() {
		if ( ! $this->is_valid() ) {
			$this->add_notice( sprintf(
					'<strong>Phanes Marketing</strong> requires your license code to be fully activated. go to: <a href="https://phanes.co/product/phanes-payment-wordpress-plugin-venmo/" target = "_blank">phanes.co</a> and buy your license today for an annual license of $39.95! <a href="%s">Add License code</a>',
					admin_url( 'admin.php?page=' . $this->slug )
				), 'error' );
		}
	}

	/**
	 * Render settings page
	 * @return void
	 */
	public function render_page() {
		require_once PM_Instance()->path . 'admin/license-page.php';
	}

	public function register_fields() {
		register_setting(
			$this->slug,
			'phanes_marketing_license',
			array( $this, 'sanitize_data' )
		);

		add_settings_section(
			'pm_license',
			'Phanes License Code',
			array( $this, 'render_section' ),
			$this->slug
		);

		add_settings_field(
			'license_code',
			'License Code',
			array( $this, 'render_text_field' ),
			$this->slug,
			'pm_license'
		);
	}

	public function render_section() {
		printf(
			'Phanes Marketing requires your license code to be fully activated. Please visit <a href="https://www.phanes.co">phanes.co</a> and buy your license today, if you did not buy yet!',
			admin_url( 'admin.php?page=' . $this->slug )
		);
	}

	public function sanitize_data( $input ) {
		$input = sanitize_text_field( $input );
		if ( $this->verify_license( $input ) ) {
			return array(
				'code'       => $input,
				'time'       => $this->response['expires'],
				'check_time' => strtotime( '+1 day' ),
			);
		}
		add_settings_error(
			'phanes_marketing_license',
			'invalid_license',
			'Invalid license. Please add a valid license code.'
		);
		return false;
	}

	public function render_text_field() {
		$license = get_option( 'phanes_marketing_license' );
		printf(
			'<input type="text" id="license_code" class="regular-text" name="phanes_marketing_license" value="%s" placeholder="Add your license key here" />',
			( $license && isset( $license['code'] ) ? $license['code'] : '' )
		);
	}

	/**
	 * Add notices
	 * @param string  $msg     Notice message
	 * @param string  $class   Additional classes
	 * @param boolean $dismiss Is the notification dissmissable
	 * @param boolean $echo
	 */
	public function add_notice( $msg, $class, $dismiss = true, $echo = false ) {

		if ( ! is_admin() ) return;

		$notice = '<div id="message" class="' . $class . ' notice is-dismissible"><p>' . $msg . '</p></div>';

		if ( $echo === true ) {
			echo $notice;
		} else {
			self::$_notices[] = $notice;
		}
	}

	/**
	 * Display saved notices
	 * @return void
	 */
	public function display_notices() {
		echo implode( "\n", self::$_notices );
	}

	/**
	 * Check license validity
	 * @return boolean
	 */
	public function is_valid() {
		$license = get_option( 'phanes_marketing_license', false );

		if ( ! $license || empty( $license ) ) {
			return false;
		}

		if ( empty( $license['code'] ) || strlen( $license['code'] ) === 20 ) {
			return false;
		}

		if ( empty( $license['time'] ) || ! is_int( $license['time'] ) || $license['time'] < strtotime( current_time( 'mysql' ) ) ) {
			return false;
		}

		if ( empty( $license['check_time'] ) || ! is_int( $license['check_time'] ) ) {
			return false;
		}

		if ( $license['check_time'] <= strtotime( current_time( 'mysql' ) ) ) {
			$check = $this->verify_license( $license['code'] );

			if ( ! $check || $check === false ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Verify license code calling license server
	 * @param  string $code The license code
	 * @return boolean
	 */
	public function verify_license( $code ) {
		require PM_Instance()->path . 'lib/wooskey-manager/wooskey-manager.php';

		$wooskey = new WoosKey_Manager( $this->api_url, $code );
		$response = $wooskey->check_license();

		if ( $response ) {
			$this->response = array(
				'code'      => $code,
				'msg'       => $wooskey->response_msg,
				'expires'   => $wooskey->response_expires,
			);

			return true;

		} elseif ( is_null( $response ) ) {
			$this->response = array(
				'code'  => $code,
				'msg'   => $wooskey->response_msg,
			);

			return null;
		}

		$this->response = array(
			'code'  => $code,
			'msg'   => $wooskey->response_msg,
		);

		return false;
	}

}

endif;
