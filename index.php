<?php
/*
Plugin Name: QRCodes
Description: Add qrcodes to pages
Author: Pierre Péronnet
Version: 2.0
*/

$uploads = wp_upload_dir();
if ( ! defined( 'QRCODES_BASEURL' ) ) {
	define( 'QRCODES_BASEURL', path_join(
		$uploads['baseurl'],
		'qrcodes'
	) );
}
if ( ! defined( 'QRCODES_BASEDIR' ) ) {
	define( 'QRCODES_BASEDIR', path_join(
		$uploads['basedir'],
		'qrcodes'
	) );
}
unset( $uploads );

require_once __DIR__ . '/qrcodes.php';
include_once __DIR__ . '/admin.php';

function wp_qrcodes_activation() {
	if ( ! wp_mkdir_p( QRCODES_BASEDIR ) ) {
		die( sprint(
			__( 'Cannot create %s.', 'qrcodes' ),
			'<i>' . QRCODES_BASEDIR . '</i>'
		) );
	}
}
register_activation_hook( __FILE__, 'wp_qrcodes_activation' );

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

function wp_qrcodes_deactivation() {
	full_remove_folder( QRCODES_BASEDIR );
}
register_deactivation_hook( __FILE__, 'wp_qrcodes_deactivation' );
