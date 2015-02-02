<?php

/*
Plugin Name: WP Reset Password Form with reCAPTCHA
Description: Add reCAPTCHA to WordPress Lost password form
Version: 1.0
Author: Ivan Kruchkoff
Author URI: http://10up.com
License: GPL2
*/


class ReCAPTCHA_Password_Reset_Form {

	private $site_key;
	private $secret;

	public function __construct() {
		$this->site_key = 'REPLACEME';
		$this->secret = 'REPLACEME';

		// adds the captcha to the password reset form
		add_action( 'lostpassword_form', array( $this, 'action_lostpassword_form' ) );
		add_action( 'login_head', array( $this, 'action_login_head' ) );

		// authenticate the captcha answer
		add_action( 'lostpassword_post', array( $this, 'action_lostpassword_post' ) );
	}


	public function action_login_head() {
		wp_enqueue_script( 'recaptcha', '//www.google.com/recaptcha/api.js' );

	}

	public function action_lostpassword_form() {
		echo "<div class='g-recaptcha' data-sitekey='{$this->site_key}'></div>";
	}



	/**
	 * Verify the captcha answer
	 *
	 * @return WP_Error
	 */
    public function action_lostpassword_post() {
		if ( ! isset( $_POST['g-recaptcha-response'] ) || empty( $_POST['g-recaptcha-response'] ) ) {
			wp_die( __( 'CAPTCHA should not be empty' ) );
		} elseif( isset( $_POST['g-recaptcha-response'] ) && $this->recaptcha_response() == 'false' ) {
			wp_die( __( 'CAPTCHA response was incorrect' ) );
        }
	}


	/**
	 * Get the reCAPTCHA API response.
	 *
	 * @return bool
	 */
	public function recaptcha_response() {

		$response  = isset( $_POST['g-recaptcha-response'] ) ? esc_attr( $_POST['g-recaptcha-response'] ) : '';

		$remote_ip = $_SERVER["REMOTE_ADDR"];

		if ( strlen( $remote_ip ) && strlen( $response ) ) {
			$response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret={$this->secret}&response={$response}&remoteip={$remote_ip}" );
			if ( $response['response']['code'] !== 200 ) {
				return false;
			}
			$body = json_decode( $response['body'], true );

			return $body[ 'success' ] == true;
		}

		return false;

	}
}

new ReCAPTCHA_Password_Reset_Form();
