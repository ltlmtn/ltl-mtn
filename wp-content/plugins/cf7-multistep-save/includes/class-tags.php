<?php
defined( 'ABSPATH' ) || exit;

class CF7MS_Tags {
	public static function init() {
		add_action( 'wpcf7_init', array( __CLASS__, 'register' ), 20 );
		add_action( 'wpcf7_admin_init', array( __CLASS__, 'tag_generator' ), 60 );
	}

	public static function register() {
		wpcf7_add_form_tag( 'step', array( __CLASS__, 'step' ), array( 'display-block' => true ) );
		wpcf7_add_form_tag( 'step-nav', array( __CLASS__, 'nav' ), array( 'display-block' => true ) );
		wpcf7_add_form_tag( 'save-session', array( __CLASS__, 'save_button' ), array( 'display-block' => true ) );
	}

	private static function enqueue() {
		wp_enqueue_style( 'cf7ms', CF7MS_URL . 'assets/cf7ms.css', array(), CF7MS_VERSION );
		wp_enqueue_script( 'cf7ms', CF7MS_URL . 'assets/cf7ms.js', array(), CF7MS_VERSION, array( 'in_footer' => true ) );
		wp_localize_script( 'cf7ms', 'cf7msConfig', array(
			'restUrl' => esc_url_raw( rest_url( 'cf7ms/v1/' ) ),
			'i18n'    => array(
				'next'      => __( 'Next', 'cf7ms' ),
				'prev'      => __( 'Back', 'cf7ms' ),
				'stepOf'    => __( 'Step %1$s of %2$s', 'cf7ms' ),
				'saved'     => __( 'Progress saved. Use this link to continue later:', 'cf7ms' ),
				'copy'      => __( 'Copy link', 'cf7ms' ),
				'copied'    => __( 'Copied!', 'cf7ms' ),
				'emailSent' => __( 'We also emailed you the link.', 'cf7ms' ),
				'error'     => __( 'Could not save your progress. Please try again.', 'cf7ms' ),
				'restored'  => __( 'Your saved progress has been restored.', 'cf7ms' ),
				'notFound'  => __( 'This saved link has expired or does not exist.', 'cf7ms' ),
				'emailLbl'  => __( 'Email me the link (optional)', 'cf7ms' ),
				'required'  => __( 'Please complete the required fields in this step.', 'cf7ms' ),
			),
		) );
	}

	public static function step( $tag ) {
		self::enqueue();
		$title = ! empty( $tag->values ) ? $tag->values[0] : '';
		return sprintf( '<div class="cf7ms-marker" data-title="%s" hidden></div>', esc_attr( $title ) );
	}

	public static function nav( $tag ) {
		self::enqueue();
		return '<div class="cf7ms-progress-slot" hidden></div>';
	}

	public static function save_button( $tag ) {
		self::enqueue();
		$label = ! empty( $tag->values ) ? $tag->values[0] : __( 'Save & get link', 'cf7ms' );
		return sprintf(
			'<div class="cf7ms-save" data-email="%d" data-autosave="%d"><button type="button" class="cf7ms-save-btn">%s</button><div class="cf7ms-save-out" role="status" aria-live="polite"></div></div>',
			$tag->has_option( 'email' ) ? 1 : 0,
			$tag->has_option( 'autosave' ) ? 1 : 0,
			esc_html( $label )
		);
	}

	public static function tag_generator() {
		if ( ! class_exists( 'WPCF7_TagGenerator' ) ) {
			return;
		}
		$gen = WPCF7_TagGenerator::get_instance();
		$cb  = array( __CLASS__, 'generator_panel' );
		$gen->add( 'step', __( 'step', 'cf7ms' ), $cb, array( 'version' => '1' ) );
		$gen->add( 'step-nav', __( 'step progress', 'cf7ms' ), $cb, array( 'version' => '1' ) );
		$gen->add( 'save-session', __( 'save session', 'cf7ms' ), $cb, array( 'version' => '1' ) );
	}

	public static function generator_panel( $contact_form, $options = '' ) {
		$help = array(
			'step'         => __( 'Starts a new step. Put on its own line: [step "Your details"]', 'cf7ms' ),
			'step-nav'     => __( 'Optional: place the progress indicator here: [step-nav]. Otherwise it is added at the top.', 'cf7ms' ),
			'save-session' => __( 'Button to save progress: [save-session "Save & get link"]. Options: email (ask for email to send the link), autosave.', 'cf7ms' ),
		);
		$id = is_array( $options ) && isset( $options['id'] ) ? $options['id'] : '';
		echo '<div class="control-box"><p>' . esc_html( $help[ $id ] ?? implode( ' ', $help ) ) . '</p></div>';
	}
}
