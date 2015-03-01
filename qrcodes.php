<?php
function qrcodes_load_domain(){
    load_plugin_textdomain(
		'qrcodes',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
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
	add_action( 'all_admin_notices', 'qrcodes_library_not_installed' );
	return false;
}
require_once path_join( QRCODES_LIB_PATH, 'qrlib.php' );

function qrcodes_get_baseurl() {
	return QRCODES_BASEURL;
}

function qrcodes_get_basedir() {
	return QRCODES_BASEDIR;
}

function qrcodes_get_cached_url( $post_id ) {
	//TODO
	return path_join(
		qrcodes_get_baseurl(),
		'post-' . $post_id . '.png'
	);
}

function qrcodes_get_cached_dir( $blog_id, $post_id ) {
	//TODO
	return path_join(
		qrcodes_get_basedir( $blog_id ),
		'post-' . $post_id . '.png'
	);
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
	$data = get_home_url( $blog_id );
	$data = apply_filters( 'qrcodes-data', $data );
	qrcodes_generate( $data );
}
add_action( 'wpmu_new_blog', 'qrcodes_new_blog' );

function qrcodes_delete_blog( $blog_id, $drop ) {
	$data = get_home_url( $blog_id );
	qrcodes_remove( $data );
}
add_action( 'wpmu_delete_blog', 'qrcodes_delete_blog' );

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

function qrcodes_remove( $data ) {
	$filename = qrcodes_data_to_filename( $data );
	@unlink( $filename );
}

function qrcodes_get_dir( $data ) {
	return path_join(
		qrcodes_get_basedir(),
		qrcodes_data_to_filename( $data )
	);
}

function qrcodes_generate( $data, $blog_id = false ) {
	$filename = qrcodes_data_to_filename( $data );
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
	QRcode::png(
		$data,
		qrcodes_get_dir( $data ),
		$correction_level,
		$matrix,
		$margin,
		false
	);
}

function qrcodes_save_post( $post_id ) {
	$data = get_permalink( $post_id );
	qrcodes_generate( $data );
}
add_action( 'save_post', 'qrcodes_save_post' );

function qrcodes_delete_post( $post_id ) {
	$filename = qrcodes_get_dir( get_permalink( $post_id ) );
	if ( ! file_exists( $filename ) ) {
		return;
	}
	if ( ! @unlink( $filename ) ) {
		add_action(
			'all_admin_notices',
			function () use ( $filename ) { ?>
				<p class="error">
					<?php printf(
						__(
							'Cannot remove file %s.',
							'qrcodes'
						),
						'<i>' . esc_html( $filename ) . '</i>'
					); ?>
				</p>
				<?php
			}
		);
	}
}
add_action( 'delete_post', 'qrcodes_delete_post' );

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
	} ?>
	<img
		src="<?php echo qrcodes_get_url( $data ); ?>"
		class="<?php echo implode(
			' ',
			apply_filters(
				'qrcodes-classes',
				array( 'qrcode' )
			)
		); ?>"
	/>
	<?php
}
add_action( 'wp_footer' , 'qrcodes_footer' );

function qrcodes_enqueue_style() {
	$media_query = get_option( 'qrcodes-network-media-query', array( 'print' ) );
	wp_enqueue_style(
		'qrcodes',
		plugins_url( 'styles/index.css', __FILE__ ),
		false,
		1.0,
		'all'
	);
	foreach ( $media_query as $medium ) {
		wp_add_inline_style(
			'qrcodes',
			'@media ' . esc_attr( $medium ) . '{' .
				'body .qrcode {' .
					'display:block;' .
					get_blog_option(
						get_current_blog_id(),
						"qrcodes-media-query-{$medium}-horizontal-direction",
						'right'
					) . ':' .
					get_blog_option(
						get_current_blog_id(),
						"qrcodes-media-query-{$medium}-horizontal-position",
						0
					) .
					get_blog_option(
						get_current_blog_id(),
						"qrcodes-media-query-{$medium}-horizontal-unit",
						'%'
					) . ';' .
					get_blog_option(
						get_current_blog_id(),
						"qrcodes-media-query-{$medium}-vertical-direction",
						'bottom'
					) . ':' .
					get_blog_option(
						get_current_blog_id(),
						"qrcodes-media-query-{$medium}-vertical-position",
						100
					) .
					get_blog_option(
						get_current_blog_id(),
						"qrcodes-media-query-{$medium}-vertical-unit",
						'%'
					) . ';' .
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

function qrcodes_shortcode_userid( $atts ) {
	return get_current_user_id();
}
add_shortcode( 'user-id', 'qrcodes_shortcode_userid' );

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
		get_option( 'qrcodes-network-override-data-value', $data )
	);
}
function qrcodes_data_force( $data ) {
	return do_shortcode(
		get_blog_option(
			get_current_blog_id(),
			'qrcodes-override-data-value',
			network_home_url()
		)
	);
}
if ( ! get_option( 'qrcodes-network-allow-override-data', true ) ) {
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
