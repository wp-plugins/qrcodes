<?php
include_once __DIR__ . '/admin_network.php';
include_once __DIR__ . '/admin_library.php';

function qrcodes_admin_page() {
	?><div class="wrap"><?php
		screen_icon();
		?><h2><?php
			_e( 'QRCodes plugin settings', 'qrcodes' );
		?></h2><?php
		?><form method="post" action="<?php echo admin_url( 'options.php' ); ?>"><?php
			settings_fields( 'qrcodes-group' );
			do_settings_sections( 'qrcodes' );
			submit_button();
		?></form>
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

function qrcodes_admin_notices() {
}

function qrcodes_section() {
	?><p><?php
		_e( 'Set options used by QRCodes plugin.', 'qrcodes' );
	?></p><?php
}

function qrcodes_admin_init() {
	add_settings_section(
		'qrcodes',
		__( 'General settings', 'qrcodes' ),
		'qrcodes_section',
		'qrcodes'
	);

	$media_query = get_option( 'qrcodes-network-media-query', array( 'print' ) );
	
	foreach ( $media_query as $medium ) {
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-active",
			'wp_validate_boolean'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-horizontal-direction",
			'qrcodes_sanitize_media_query_horizontal_direction'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-horizontal-position",
			'absint'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-horizontal-unit",
			'qrcodes_sanitize_media_query_unit'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-vertical-direction",
			'qrcodes_sanitize_media_query_vertical_direction'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-vertical-position",
			'absint'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-vertical-unit",
			'qrcodes_sanitize_media_query_unit'
		);
	}
	add_settings_field(
		'qrcodes-media-query',
		__( 'Media query', 'qrcodes' ),
		'qrcodes_display_media_query',
		'qrcodes',
		'qrcodes',
		$media_query
	);

	register_setting(
		'qrcodes-group',
		'qrcodes-redirect-404',
		'wp_validate_boolean'
	);
	add_settings_field(
		'qrcodes-redirect-404',
		__( 'Redirect 404 to home page', 'qrcodes' ),
		'qrcodes_display_redirect_404',
		'qrcodes',
		'qrcodes',
		'qrcodes-redirect-404'
	);

	register_setting(
		'qrcodes-group',
		'qrcodes-override-data-value'
	);
	add_settings_field(
		'qrcodes-override-data-value',
		__( 'Qrcodes\' link', 'qrcodes' ),
		'qrcodes_display_override_data_value',
		'qrcodes',
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
		'qrcodes',
		'qrcodes',
		'qrcodes-replace-thumbnail'
	);
}
add_action( 'admin_init', 'qrcodes_admin_init', 7 );

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
	$value = get_blog_option( get_current_blog_id(), $name, true ); ?>
	<input
		type="checkbox"
		name="<?php echo esc_attr( $name ); ?>"
		<?php checked( $value ); ?>
	/>
	<?php
}

function qrcodes_display_override_data_value( $name ) {
	$enabled = get_option( 'qrcodes-network-override-data-allow', true );
	$value = $enabled ?
		get_blog_option( get_current_blog_id(), $name, '[current-url]' ) :
		get_option( 'qrcodes-network-override-data-value', network_home_url() );
	?>
	<input
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		<?php disabled(
			$enabled,
			false
		); ?>
	/>
	<?php
}

function qrcodes_display_media_query( $media ) {
	$units = array(
		'%',
		'px',
	);
	?><ul><?php
		foreach ( $media as $medium ) {
			$active = get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-active"
			);
			$h_dir = get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-horizontal-direction",
				'right'
			);
			$h_pos = get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-horizontal-position",
				0
			);
			$h_unit = get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-horizontal-unit",
				'%'
			);
			$v_dir = get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-vertical-direction",
				'bottom'
			);
			$v_pos = get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-vertical-position",
				100
			);
			$v_unit = get_blog_option(
				get_current_blog_id(),
				"qrcodes-media-query-{$medium}-vertical-unit",
				'%'
			);
			$medium = esc_attr( $medium );
			?><li>
					<input
						id="<?php echo "qrcodes-media-query-{$medium}-active"; ?>"
						name="<?php echo "qrcodes-media-query-{$medium}-active"; ?>"
						type="checkbox"
						<?php checked( $active ); ?> />
					<label
						for="<?php echo "qrcodes-media-query-{$medium}-active"; ?>"
					><?php
						echo esc_html( $medium );
					?></label>
					<div class="more-options">
						<select
							id="<?php echo "qrcodes-media-query-{$medium}-horizontal-direction"; ?>"
							name="<?php echo "qrcodes-media-query-{$medium}-horizontal-direction"; ?>"
						>
							<option value="left" <?php selected( $h_dir, 'left' ); ?>><?php
								esc_html_e( 'left', 'qrcodes' );
							?></option>
							<option value="right" <?php selected( $h_dir, 'right' ); ?>><?php
								esc_html_e( 'right', 'qrcodes' );
							?></option>
						</select>
						<input
							type="number"
							name="<?php echo "qrcodes-media-query-{$medium}-horizontal-position"; ?>"
							value="<?php echo esc_attr( $h_pos ); ?>"
						/>
						<select
							id="<?php echo "qrcodes-media-query-{$medium}-horizontal-unit"; ?>"
							name="<?php echo "qrcodes-media-query-{$medium}-horizontal-unit"; ?>"
						><?php
							foreach ( $units as $unit ) {
								?><option
									value="<?php echo esc_attr( $unit ); ?>"
									<?php selected( $unit, $h_unit ); ?>
								><?php
									echo esc_html( $unit );
								?></option><?php
							}
						?></select>
						<select
							id="<?php echo "qrcodes-media-query-{$medium}-vertical-direction"; ?>"
							name="<?php echo "qrcodes-media-query-{$medium}-vertical-direction"; ?>"
						>
							<option value="top" <?php selected( $v_dir, 'top' ); ?>><?php
								esc_html_e( 'top', 'qrcodes' );
							?></option>
							<option value="bottom" <?php selected( $v_dir, 'bottom' ); ?>><?php
								esc_html_e( 'bottom', 'qrcodes' );
							?></option>
						</select>
						<input
							type="number"
							name="<?php echo "qrcodes-media-query-{$medium}-vertical-position"; ?>"
							value="<?php echo esc_attr( $v_pos ); ?>"
						/>
						<select
							id="<?php echo "qrcodes-media-query-{$medium}-vertical-unit"; ?>"
							name="<?php echo "qrcodes-media-query-{$medium}-vertical-unit"; ?>"
						><?php
							foreach ( $units as $unit ) {
								?><option
									value="<?php echo esc_attr( $unit ); ?>"
									<?php selected( $unit, $v_unit ); ?>
								><?php
									echo esc_html( $unit );
								?></option><?php
							}
						?></select>
					</div>
			</li><?php
		}
	?></ul><?php
}

function qrcodes_sanitize_media_query_horizontal_direction( $unit ) {
	$units = array(
		'left',
		'right',
	);
	if ( ! in_array( $unit, $units ) ) {
		$unit = 'right';
	}
	return $unit;
}

function qrcodes_sanitize_media_query_vertical_direction( $unit ) {
	$units = array(
		'bottom',
		'top',
	);
	if ( ! in_array( $unit, $units ) ) {
		$unit = 'bottom';
	} 
	return $unit;
}

function qrcodes_sanitize_media_query_unit( $unit ) {
	$units = array(
		'%',
		'px',
	);
	if ( ! in_array( $unit, $units ) ) {
		$unit = '%';
	} 
	return $unit;
}

function qrcodes_enqueue_admin_style() {
	wp_enqueue_style(
		'qrcodes-admin',
		plugins_url( 'styles/admin.css', __FILE__ ),
		false,
		1.0,
		'all'
	);
}
add_action( 'admin_enqueue_scripts', 'qrcodes_enqueue_admin_style' );
