<?php
/**
 * Plugin Name: CF7 Multi-Step & Save Progress
 * Description: Extends Contact Form 7 with multi-step form tags and account-free save/resume of unfinished forms via a unique link.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Requires Plugins: contact-form-7
 * License: GPL-2.0-or-later
 * Text Domain: cf7ms
 */

defined( 'ABSPATH' ) || exit;

define( 'CF7MS_VERSION', '1.0.0' );
define( 'CF7MS_DIR', plugin_dir_path( __FILE__ ) );
define( 'CF7MS_URL', plugin_dir_url( __FILE__ ) );

require_once CF7MS_DIR . 'includes/class-sessions.php';
require_once CF7MS_DIR . 'includes/class-tags.php';
require_once CF7MS_DIR . 'includes/class-rest.php';

register_activation_hook( __FILE__, array( 'CF7MS_Sessions', 'install' ) );
register_deactivation_hook( __FILE__, function () {
	wp_clear_scheduled_event( 'cf7ms_purge' );
} );

add_action( 'plugins_loaded', function () {
	if ( ! defined( 'WPCF7_VERSION' ) ) {
		return;
	}
	CF7MS_Sessions::maybe_upgrade();
	CF7MS_Tags::init();
	CF7MS_Rest::init();

	if ( ! wp_next_scheduled( 'cf7ms_purge' ) ) {
		wp_schedule_event( time() + HOUR_IN_SECONDS, 'daily', 'cf7ms_purge' );
	}
	add_action( 'cf7ms_purge', array( 'CF7MS_Sessions', 'purge_expired' ) );

	// Delete the draft once the form has been submitted successfully.
	add_action( 'wpcf7_mail_sent', function ( $form ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- CF7 verifies the request.
		$key = isset( $_POST['_cf7ms_key'] ) ? sanitize_text_field( wp_unslash( $_POST['_cf7ms_key'] ) ) : '';
		if ( CF7MS_Sessions::valid_key( $key ) ) {
			CF7MS_Sessions::delete( $key, $form->id() );
		}
	} );

	add_action( 'admin_init', function () {
		if ( function_exists( 'wp_add_privacy_policy_content' ) ) {
			wp_add_privacy_policy_content(
				'CF7 Multi-Step & Save Progress',
				wp_kses_post( '<p>When a visitor saves an unfinished form, the entered values (and, if provided, a hash of their email address) are stored on this site under a random key for up to ' . CF7MS_Sessions::expiry_days() . ' days, or until the form is submitted. Anyone holding the saved link can resume the form.</p>' )
			);
		}
	} );

	add_filter( 'wp_privacy_personal_data_erasers', function ( $erasers ) {
		$erasers['cf7ms'] = array(
			'eraser_friendly_name' => 'CF7 saved form progress',
			'callback'             => array( 'CF7MS_Sessions', 'erase_by_email' ),
		);
		return $erasers;
	} );
} );
