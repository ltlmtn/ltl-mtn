<?php
defined( 'ABSPATH' ) || exit;

class CF7MS_Rest {
	const ALLOWED_TYPES = array( 'text', 'textarea', 'email', 'url', 'tel', 'number', 'range', 'date', 'select', 'checkbox', 'radio' );

	public static function init() {
		add_action( 'rest_api_init', array( __CLASS__, 'routes' ) );
	}

	public static function routes() {
		register_rest_route( 'cf7ms/v1', '/save', array(
			'methods'             => 'POST',
			'callback'            => array( __CLASS__, 'save' ),
			'permission_callback' => '__return_true',
		) );
		register_rest_route( 'cf7ms/v1', '/load', array(
			'methods'             => 'GET',
			'callback'            => array( __CLASS__, 'load' ),
			'permission_callback' => '__return_true',
		) );
	}

	private static function throttle( $bucket, $limit ) {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$k  = 'cf7ms_' . $bucket . '_' . md5( $ip );
		$n  = (int) get_transient( $k );
		if ( $n >= $limit ) {
			return false;
		}
		set_transient( $k, $n + 1, HOUR_IN_SECONDS );
		return true;
	}

	/** Counts an event under an arbitrary id (e.g. recipient hash); false once the limit is reached. */
	private static function throttle_id( $bucket, $id, $limit, $ttl ) {
		$k = 'cf7ms_' . $bucket . '_' . md5( $id );
		$n = (int) get_transient( $k );
		if ( $n >= $limit ) {
			return false;
		}
		set_transient( $k, $n + 1, $ttl );
		return true;
	}

	private static function field_names( $form_id ) {
		$form = function_exists( 'wpcf7_contact_form' ) ? wpcf7_contact_form( $form_id ) : null;
		if ( ! $form ) {
			return null;
		}
		$names = array();
		foreach ( $form->scan_form_tags() as $tag ) {
			if ( $tag->name && in_array( $tag->basetype, self::ALLOWED_TYPES, true ) ) {
				$names[ $tag->name ] = true;
			}
		}
		return $names;
	}

	private static function clean( $v ) {
		return is_array( $v )
			? array_map( array( __CLASS__, 'clean' ), array_slice( $v, 0, 100 ) )
			: sanitize_textarea_field( (string) $v );
	}

	public static function save( WP_REST_Request $req ) {
		$form_id = absint( $req->get_param( 'form_id' ) );
		$names   = self::field_names( $form_id );
		if ( null === $names ) {
			return new WP_Error( 'cf7ms_form', 'Unknown form', array( 'status' => 404 ) );
		}
		if ( ! self::throttle( 'save', 60 ) ) {
			return new WP_Error( 'cf7ms_rate', 'Too many requests', array( 'status' => 429 ) );
		}

		$raw  = $req->get_param( 'data' );
		$data = array();
		if ( is_array( $raw ) ) {
			foreach ( $raw as $name => $value ) {
				if ( isset( $names[ $name ] ) && ( is_scalar( $value ) || is_array( $value ) ) ) {
					$data[ $name ] = self::clean( $value );
				}
			}
		}

		$key = (string) $req->get_param( 'key' );
		$key = CF7MS_Sessions::valid_key( $key ) ? $key : '';
		$email = sanitize_email( (string) $req->get_param( 'email' ) );

		$key = CF7MS_Sessions::save( $key, $form_id, $data, absint( $req->get_param( 'step' ) ), $email );
		if ( ! $key ) {
			return new WP_Error( 'cf7ms_save', 'Could not save', array( 'status' => 400 ) );
		}

		$page = wp_validate_redirect( esc_url_raw( (string) $req->get_param( 'url' ) ), home_url( '/' ) );
		$link = add_query_arg( 'cf7s', $key, remove_query_arg( 'cf7s', $page ) );

		$emailed = false;
		if (
			$email && is_email( $email )
			&& self::throttle( 'mail', 5 )
			&& self::throttle_id( 'mail_to', strtolower( $email ), 3, DAY_IN_SECONDS )
			&& self::throttle_id( 'mail_all', 'site', (int) apply_filters( 'cf7ms_site_mail_limit_per_hour', 30 ), HOUR_IN_SECONDS )
		) {
			$emailed = wp_mail(
				$email,
				sprintf( /* translators: %s: site name */ __( 'Continue your form on %s', 'cf7ms' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ) ),
				sprintf( __( "Use this link to continue where you left off:\n\n%1\$s\n\nThe link works until %2\$s.", 'cf7ms' ), $link, wp_date( get_option( 'date_format' ), time() + CF7MS_Sessions::expiry_days() * DAY_IN_SECONDS ) )
			);
		}

		return array( 'key' => $key, 'link' => $link, 'emailed' => (bool) $emailed );
	}

	public static function load( WP_REST_Request $req ) {
		$key = (string) $req->get_param( 'key' );
		if ( ! CF7MS_Sessions::valid_key( $key ) ) {
			return new WP_Error( 'cf7ms_key', 'Not found', array( 'status' => 404 ) );
		}
		if ( ! self::throttle( 'load', 120 ) ) {
			return new WP_Error( 'cf7ms_rate', 'Too many requests', array( 'status' => 429 ) );
		}
		$row = CF7MS_Sessions::get( $key, absint( $req->get_param( 'form_id' ) ) );
		if ( ! $row ) {
			return new WP_Error( 'cf7ms_key', 'Not found', array( 'status' => 404 ) );
		}
		$res = rest_ensure_response( array(
			'data' => json_decode( $row['data'], true ),
			'step' => (int) $row['step'],
		) );
		$res->header( 'Cache-Control', 'no-store' );
		return $res;
	}
}
