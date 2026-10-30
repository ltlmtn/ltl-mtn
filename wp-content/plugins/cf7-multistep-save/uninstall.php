<?php
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
global $wpdb;
// phpcs:ignore WordPress.DB.DirectDatabaseQuery
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}cf7ms_sessions" );
delete_option( 'cf7ms_db_version' );
wp_clear_scheduled_hook( 'cf7ms_purge' );
