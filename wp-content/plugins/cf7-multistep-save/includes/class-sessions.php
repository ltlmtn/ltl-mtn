<?php
defined( 'ABSPATH' ) || exit;

class CF7MS_Sessions {
	const DB_VERSION = '1';
	const MAX_BYTES  = 262144;

	public static function table() {
		global $wpdb;
		return $wpdb->prefix . 'cf7ms_sessions';
	}

	public static function install() {
		global $wpdb;
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		$charset = $wpdb->get_charset_collate();
		dbDelta( 'CREATE TABLE ' . self::table() . " (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			session_key char(32) NOT NULL,
			form_id bigint(20) unsigned NOT NULL,
			data longtext NOT NULL,
			step smallint(5) unsigned NOT NULL DEFAULT 0,
			email_hash char(64) NOT NULL DEFAULT '',
			created datetime NOT NULL,
			updated datetime NOT NULL,
			expires datetime NOT NULL,
			PRIMARY KEY  (id),
			UNIQUE KEY session_key (session_key),
			KEY expires (expires),
			KEY email_hash (email_hash)
		) $charset;" );
		update_option( 'cf7ms_db_version', self::DB_VERSION );
	}

	public static function maybe_upgrade() {
		if ( get_option( 'cf7ms_db_version' ) !== self::DB_VERSION ) {
			self::install();
		}
	}

	public static function expiry_days() {
		return max( 1, (int) apply_filters( 'cf7ms_expiry_days', 30 ) );
	}

	public static function valid_key( $key ) {
		return is_string( $key ) && (bool) preg_match( '/^[a-f0-9]{32}$/', $key );
	}

	public static function email_hash( $email ) {
		return $email ? hash_hmac( 'sha256', strtolower( trim( $email ) ), wp_salt( 'auth' ) ) : '';
	}

	public static function get( $key, $form_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		return $wpdb->get_row( $wpdb->prepare(
			'SELECT * FROM ' . self::table() . ' WHERE session_key = %s AND form_id = %d AND expires > %s',
			$key,
			$form_id,
			current_time( 'mysql', true )
		), ARRAY_A );
	}

	/** Creates or updates a session; returns its key or false. */
	public static function save( $key, $form_id, array $data, $step, $email = '' ) {
		global $wpdb;
		$json = wp_json_encode( $data );
		if ( ! $json || strlen( $json ) > self::MAX_BYTES ) {
			return false;
		}
		$now     = current_time( 'mysql', true );
		$expires = gmdate( 'Y-m-d H:i:s', time() + self::expiry_days() * DAY_IN_SECONDS );
		$hash    = self::email_hash( $email );

		if ( $key && self::get( $key, $form_id ) ) {
			$row = array( 'data' => $json, 'step' => (int) $step, 'updated' => $now, 'expires' => $expires );
			if ( $hash ) {
				$row['email_hash'] = $hash;
			}
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery
			$wpdb->update( self::table(), $row, array( 'session_key' => $key ) );
			return $key;
		}

		$key = bin2hex( random_bytes( 16 ) );
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$ok = $wpdb->insert( self::table(), array(
			'session_key' => $key,
			'form_id'     => $form_id,
			'data'        => $json,
			'step'        => (int) $step,
			'email_hash'  => $hash,
			'created'     => $now,
			'updated'     => $now,
			'expires'     => $expires,
		) );
		return $ok ? $key : false;
	}

	public static function delete( $key, $form_id ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->delete( self::table(), array( 'session_key' => $key, 'form_id' => (int) $form_id ) );
	}

	public static function purge_expired() {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$wpdb->query( $wpdb->prepare( 'DELETE FROM ' . self::table() . ' WHERE expires < %s', current_time( 'mysql', true ) ) );
	}

	public static function erase_by_email( $email ) {
		global $wpdb;
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery
		$n = (int) $wpdb->delete( self::table(), array( 'email_hash' => self::email_hash( $email ) ) );
		return array( 'items_removed' => $n > 0, 'items_retained' => false, 'messages' => array(), 'done' => true );
	}
}
