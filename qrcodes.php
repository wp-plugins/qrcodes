<?php
function qrcodes_load_domain(){
    load_plugin_textdomain(
		'qrcodes',
		false,
		path_join(
			dirname( plugin_basename( QRCODES_INDEX_FILE ) ),
			'languages'
		)
	);
}
add_action( 'init', 'qrcodes_load_domain' );

function qrcodes_library_not_installed() {
	?><div class="error"><p><?php
		_e(
			'QRCode PHP library is not installed. Please specify <i>QRCODES_LIB_PATH</i> constant in <i>wp_config.php</i>.',
			'qrcodes'
		);
	?></p></div><?php
}

if ( ! defined( 'QRCODES_LIB_PATH' ) ) {
	$path = path_join(
		plugin_dir_path( QRCODES_INDEX_FILE ),
		'phpqrcode'
	);
	if ( ! is_dir( $path ) ) {
		add_action(
			'all_admin_notices',
			'qrcodes_library_not_installed'
		);
		return false;
	}
	define( 'QRCODES_LIB_PATH', $path );
	unset( $path );
}
require_once path_join( QRCODES_LIB_PATH, 'qrlib.php' );

include_once path_join(
	plugin_dir_path( QRCODES_INDEX_FILE ),
	'includes/blog_index.php'
);

function qrcodes_get_baseurl() {
	return QRCODES_BASEURL;
}

function qrcodes_get_basedir() {
	return QRCODES_BASEDIR;
}

$qrcodes_current_page_dir;
$qrcodes_current_page_url;

function qrcodes_regenerate_all() {
	foreach ( get_all_page_ids() as $page_id ) {
		qrcodes_save_post( $page_id );
	}
}

function qrcodes_get_current_url() {
	return path_join(
		'http' . ( isset( $_SERVER['HTTPS'] ) ? 's' : '' ) . '://' .
		"{$_SERVER['HTTP_HOST']}",
		ltrim( $_SERVER['REQUEST_URI'], '/' )
	);
}

function qrcodes_get_404_dir( $blog_id = false ) {
	if ( false === $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	return path_join(
		QRCODES_BASEDIR,
		"error_404_{$blog_id}.png"
	);
}

function qrcodes_get_404_url( $blog_id = false ) {
	if ( false === $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	return path_join(
		QRCODES_BASEURL,
		"error_404_{$blog_id}.png"
	);
}

function qrcodes_new_blog(
		$blog_id,
		$user_id = null,
		$domain  = null,
		$path    = null,
		$site_id = null,
		$meta    = null
	) {
	/*
	remove_shortcode( 'current-url' );
	add_shortcode(
		'current-url',
		function ( $atts ) use ( $url ) {
			$atts = shortcode_atts( array(
				'encode' => 'false',
			), $atts, 'current-url' );
			if ( wp_validate_boolean( $atts['encode'] ) ) {
				$url = urlencode( $url );
			}
			return $url;
		}
	);
	remove_shortcode( 'user-id' );
	add_shortcode(
		'user-id',
		function ( $atts ) {
			return '0';
		}
	);
	remove_shortcode( 'blog-id' );
	add_shortcode(
		'blog-id',
		function ( $atts ) use ( $blog_id ) {
			return $blog_id;
		}
	);*/
	$url = get_home_url( $blog_id );
	$data = apply_filters( 'qrcodes-data', $url );
	qrcodes_generate( $data );
}
add_action( 'wpmu_new_blog', 'qrcodes_new_blog' );

function qrcodes_get_current_qrcodes_dir() {
	global $qrcodes_current_page_dir;

	return $qrcodes_current_page_dir;
}

function qrcodes_get_current_qrcodes_url() {
	global $qrcodes_current_page_url;

	return $qrcodes_current_page_url;
}

function qrcodes_data_to_filename( $data ) {
	return md5( $data ) . '.png';
}

function qrcodes_get_dir( $data ) {
	return path_join(
		qrcodes_get_basedir(),
		qrcodes_data_to_filename( $data )
	);
}

function qrcodes_generate( $data, $blog_id = false ) {
	if ( false === $blog_id ) {
		$blog_id = get_current_blog_id();
	}

	$correction_level = get_blog_option(
		$blog_id,
		'qrcodes-library-correction-level',
		QR_ECLEVEL_M
	);
	$matrix = get_blog_option(
		$blog_id,
		'qrcodes-library-matrix-point-size',
		3
	);
	$margin = get_blog_option(
		$blog_id,
		'qrcodes-library-margin',
		4
	);
	$path = qrcodes_get_dir( $data );

	QRcode::png(
		$data,
		$path,
		$correction_level,
		$matrix,
		$margin,
		false
	);
	do_action(
		'qrcodes-generate',
		$blog_id,
		$data,
		$path,
		$correction_level,
		$matrix,
		$margin,
		false
	);
}

function qrcodes_exists( $data ) {
	$path = qrcodes_get_dir( $data );
	return file_exists( $path ) && is_file( $path );
}

function qrcodes_remove( $data, $blog_id = false ) {
	if ( false === $blog_id ) {
		$blog_id = get_current_blog_id();
	}
	$path = qrcodes_get_dir( $data );

	if (
			! file_exists( $path ) ||
			! unlink( $path )
		) {
		return false;
	}
	do_action(
		'qrcodes-remove',
		$blog_id,
		$data,
		$path
	);
	return true;
}

function qrcodes_save_post( $post_id ) {
	$data = get_permalink( $post_id );
	qrcodes_generate( $data );
}
add_action( 'save_post', 'qrcodes_save_post' );

function qrcodes_get_url( $data ) {
	return path_join(
		qrcodes_get_baseurl(),
		qrcodes_data_to_filename( $data )
	);
}

function qrcodes_footer() {
	$data = apply_filters(
		'qrcodes-current',
		qrcodes_get_current_url()
	);
	$path = qrcodes_get_dir( $data );
	if ( ! file_exists( $path ) ) {
		qrcodes_generate( $data );
	}
	?><img
		src="<?php echo qrcodes_get_url( $data ); ?>"
		class="<?php echo implode(
			' ',
			apply_filters(
				'qrcodes-classes',
				array( 'qrcodes' )
			)
		); ?>"
	/><?php
}
add_action( 'wp_footer' , 'qrcodes_footer' );

function qrcodes_enqueue_style() {
	$media_query = get_option( QRCODES_MEDIA_QUERY_OPTION_NAME, array() );
	wp_enqueue_style(
		'qrcodes',
		plugins_url( 'styles/index.css', __FILE__ ),
		false,
		1.0,
		'all'
	);
	foreach ( $media_query as $medium => $desc ) {
		$style =
			'display:block;' .
			get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-horizontal-direction"
			) . ':' .
			get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-horizontal-value"
			) . ';' .
			get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-vertical-direction"
			) . ':' .
			get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-vertical-value"
			) . ';';
		$size = get_blog_option(
			get_current_blog_id(),
			"qrcodes-media-query-{$medium}-size"
		);
		if ( $auto_size ) {
			$style .=
				'width:' .
				get_blog_option(
					get_current_blog_id(),
					"qrcodes-media-query-{$medium}-size"
				) . ';' .
				'height:' .
				get_blog_option(
					get_current_blog_id(),
					"qrcodes-media-query-{$medium}-size"
				) . ';';
		}
		wp_add_inline_style(
			'qrcodes',
			'@media ' . esc_attr( $medium ) . '{' .
				'body .qrcodes {' .
					$style .
				'}' .
			'}'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'qrcodes_enqueue_style' );

function qrcodes_shortcode_blogid( $atts ) {
	return get_current_blog_id();
}
add_shortcode( 'blog-id', 'qrcodes_shortcode_blogid' );

/*
function qrcodes_shortcode_userid( $atts ) {
	return get_current_user_id();
}
add_shortcode( 'user-id', 'qrcodes_shortcode_userid' );
*/

function qrcodes_shortcode_currenturl( $atts ) {
	$atts = shortcode_atts( array(
		'encode' => 'false',
	), $atts, 'current-url' );
	$url = qrcodes_get_current_url();
	if ( wp_validate_boolean( $atts['encode'] ) ) {
		$url = urlencode( $url );
	}
	return $url;
}
add_shortcode( 'current-url', 'qrcodes_shortcode_currenturl' );

function qrcodes_network_data_force( $data ) {
	return do_shortcode(
		get_option(
			'qrcodes-network-override-data-value',
			network_home_url()
		)
	);
}
function qrcodes_data_force( $data ) {
	$value = get_blog_option(
			get_current_blog_id(),
			'qrcodes-override-data-value',
			network_home_url()
		);
	$value = do_shortcode( $value );
	return $value;
}
if ( ! get_option( 'qrcodes-network-override-data-allow', true ) ) {
	add_filter( 'qrcodes-data', 'qrcodes_network_data_force', 30 );
} else {
	add_filter( 'qrcodes-data', 'qrcodes_data_force' );
}

function qrcodes_data_404( $data ) {
	return is_404() ? home_url() : $data;
}
if ( ! get_option( 'qrcodes-redirect-404', true ) ) {
	add_filter( 'qrcodes-data', 'qrcodes_data_404', 3 );
}

function qrcodes_data_permalink( $data ) {
	return is_singular() ? get_permalink() : $data;
}
add_filter( 'qrcodes-current', 'qrcodes_data_permalink', 3 );
