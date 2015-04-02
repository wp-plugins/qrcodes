<?php
include_once path_join( __DIR__, 'admin-network.php' );
include_once path_join( __DIR__, 'admin-library.php' );
include_once path_join( __DIR__, 'admin-media-query.php' );

function qrcodes_admin_page() {
	?><div class="wrap"><?php
		?><h2><?php
			_e( 'QRCodes plugin settings', 'qrcodes' );
		?></h2><?php
		?><form method="post" action="<?php echo admin_url( 'options.php' ); ?>"><?php
			$pages = apply_filters(
				'qrcodes-admin-page',
				array(
					'general' => array(
						'menu'     => __( 'General', 'qrcodes' ),
						'sections' => 'qrcodes-general',
					),
				)
			);
			settings_fields( 'qrcodes-group' );
			?><h2 class="nav-tab-wrapper"><?php
				foreach ( $pages as $id => $page ) {
						?><a class="nav-tab" href="#qrcodes-page-<?php echo esc_attr( $id ); ?>"><?php
							echo esc_html( $page['menu'] );
						?></a><?php
				} ?>
			</h2>
			<div id="poststuff">
				<div id="post-body"><?php
					foreach ( $pages as $id => $page ) {
						?><div
							id="qrcodes-page-<?php echo esc_attr( $id ); ?>"
							class="postbox-container"
						><div class="postbox"><?php
							do_settings_sections( $page['sections'] );
						?></div></div><?php
					}
					submit_button();
				?></div>
			</div>
		</form>
	</div><?php
}

function qrcodes_admin_menu() {
	$slug = add_options_page(
		__( 'QRCodes', 'qrcodes' ),
		__( 'QRCodes', 'qrcodes' ),
		'manage_options',
		'qrcodes',
		'qrcodes_admin_page'
	);
	add_action( "load-{$slug}", 'qrcodes_admin_load' );
}
add_action( 'admin_menu', 'qrcodes_admin_menu' );

function qrcodes_admin_load() {
	add_action(
		'admin_notices',
		'qrcodes_admin_notices'
	);
}

function qrcodes_admin_script( $page ) {
	if ( 'settings_page_qrcodes' == $page ) {
		wp_enqueue_script(
			'qrcodes-admin',
			plugins_url( path_join( '/script', 'admin.js' ), QRCODES_INDEX_FILE ),
			array( 'jquery' ),
			'0.1',
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'qrcodes_admin_script' );

function qrcodes_admin_notices() {
}

function qrcodes_section() {
	?><p><?php
		_e( 'Set options used by QRCodes plugin.', 'qrcodes' );
	?></p><?php
}

function qrcodes_admin_init() {
	/** General section **/
	add_settings_section(
		'qrcodes',
		__( 'General settings', 'qrcodes' ),
		'qrcodes_section',
		'qrcodes-general'
	);

	$allow_override = get_option(
		'qrcodes-network-override-data-allow',
		true
	);

	if ( $allow_override ) {
		register_setting(
			'qrcodes-group',
			'qrcodes-redirect-404',
			'wp_validate_boolean'
		);
	}
	add_settings_field(
		'qrcodes-redirect-404',
		__( 'Redirect 404 to home page', 'qrcodes' ),
		'qrcodes_display_redirect_404',
		'qrcodes-general',
		'qrcodes',
		'qrcodes-redirect-404'
	);

	if ( $allow_override ) {
		register_setting(
			'qrcodes-group',
			'qrcodes-override-data-value',
			'qrcodes_admin_sanitize_override_value'
		);
	}
	add_settings_field(
		'qrcodes-override-data-value',
		__( 'Qrcodes\' link', 'qrcodes' ),
		'qrcodes_display_override_data_value',
		'qrcodes-general',
		'qrcodes',
		'qrcodes-override-data-value'
	);

	register_setting(
		'qrcodes-group',
		'qrcodes-replace-thumbnail',
		'wp_validate_boolean'
	);
	add_settings_field(
		'qrcodes-replace-thumbnail',
		__( 'Replace thumbnail\'s articles', 'qrcodes' ),
		'qrcodes_display_replace_thumbnail',
		'qrcodes-general',
		'qrcodes',
		'qrcodes-replace-thumbnail'
	);
}
add_action( 'admin_init', 'qrcodes_admin_init', 7 );

function qrcodes_admin_sanitize_override_value( $value ) {
	if ( empty( $value ) ) {
		return false;
	}
	return $value;
}

function qrcodes_display_replace_thumbnail( $name ) {
	$value = get_blog_option( get_current_blog_id(), $name, false ); ?>
	<input
		type="checkbox"
		name="<?php echo esc_attr( $name ); ?>"
		<?php checked( $value ); ?>
	/>
	<?php
}

function qrcodes_display_redirect_404( $name ) {
	$enabled = get_option(
		'qrcodes-network-override-data-allow',
		true
	);
	$value   = get_blog_option( get_current_blog_id(), $name, true ); ?>
	<input
		type="checkbox"
		name="<?php echo esc_attr( $name ); ?>"
		<?php
			checked( $value );
			disabled( $enabled, false );
		?>
	/>
	<?php
}

function qrcodes_display_override_data_value( $name ) {
	$enabled = get_option(
		'qrcodes-network-override-data-allow',
		true
	);
	$value   = $enabled ?
		get_blog_option(
			get_current_blog_id(),
			$name,
			'[current-url]'
		) :
		get_option(
			'qrcodes-network-override-data-value',
			network_home_url()
		);
	?><input
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="[current-url]"
		autocomplete="off"
		<?php disabled( $enabled, false ); ?>
	/><?php
}

function qrcodes_enqueue_admin_style() {
	wp_enqueue_style(
		'qrcodes-admin',
		plugins_url( 'styles/admin.css', QRCODES_INDEX_FILE ),
		false,
		1.0,
		'all'
	);
}
add_action( 'admin_enqueue_scripts', 'qrcodes_enqueue_admin_style' );
