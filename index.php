<?php
/*
Plugin Name: QRCodes
Description: Add qrcodes to pages
Author: Pierre Péronnet
Version: 1.3
*/

$uploads = wp_upload_dir();
if ( ! defined( 'QRCODES_BASEURL' ) ) {
	/**
	 * @brief 
	 * 
	 * 
	 */
	define( 'QRCODES_BASEURL', path_join(
		$uploads['baseurl'],
		'qrcodes'
	) );
}
if ( ! defined( 'QRCODES_BASEDIR' ) ) {
	/**
	 * @brief 
	 * 
	 * 
	 */
	define( 'QRCODES_BASEDIR', path_join(
		$uploads['basedir'],
		'qrcodes'
	) );
}
/**
 * @brief 
 * 
 * 
 */
define( 'QRCODES_INDEX_FILE', __FILE__ );

unset( $uploads );

/**
 * @brief 
 * @returns 
 * 
 * 
 */
function wp_qrcodes_activation() {
	if ( ! wp_mkdir_p( QRCODES_BASEDIR ) ) {
		die( sprint(
			__( 'Cannot create %s.', 'qrcodes' ),
			'<i>' . QRCODES_BASEDIR . '</i>'
		) );
	}

	add_option( 'qrcodes-network-media-query', array(
		'print' => __( 'displayed on printed pages', 'qrcodes' ),
	), '', 'yes' );
	qrcodes_media_query_add_default_options( 'print' );
}
register_activation_hook( QRCODES_INDEX_FILE, 'wp_qrcodes_activation' );


/**
 * @brief 
 * @param $medium 
 * @returns 
 * 
 * 
 */
function qrcodes_media_query_remove_options( $medium ) {
	$blog_id = get_current_blog_id();
	delete_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-horizontal-direction"
	);
	delete_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-horizontal-value"
	);
	delete_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-vertical-direction"
	);
	delete_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-vertical-value"
	);
	delete_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-size"
	);
}

/**
 * @brief 
 * @param $medium 
 * @returns 
 * 
 * 
 */
function qrcodes_media_query_add_default_options( $medium ) {
	$blog_id = get_current_blog_id();
	add_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-horizontal-direction",
		'right',
		true
	);
	add_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-horizontal-value",
		0,
		true
	);
	add_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-vertical-direction",
		'top',
		true
	);
	add_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-vertical-value",
		0,
		true
	);
	add_blog_option(
		$blog_id,
		"qrcodes-media-query-{$medium}-size",
		false,
		true
	);
}

/**
 * @brief 
 * @param $dir 
 * @returns 
 * 
 * 
 */
function full_remove_folder( $dir ) {
	$it = new RecursiveDirectoryIterator(
		$dir,
		RecursiveDirectoryIterator::SKIP_DOTS
	);
	$files = new RecursiveIteratorIterator(
		$it,
		RecursiveIteratorIterator::CHILD_FIRST
	);
	foreach ( $files as $file ) {
		if ( $file->isDir() ){
			rmdir( $file->getRealPath() );
		} else {
			unlink( $file->getRealPath() );
		}
	}
	rmdir( $dir );
}

/**
 * @brief 
 * @returns 
 * 
 * 
 */
function wp_qrcodes_deactivation() {
	full_remove_folder( QRCODES_BASEDIR );
}
register_deactivation_hook( QRCODES_INDEX_FILE, 'wp_qrcodes_deactivation' );

require_once path_join( __DIR__, 'qrcodes.php' );
include_once path_join( __DIR__, 'includes/admin.php' );
