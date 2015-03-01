<?php
function qrcodes_admin_network_menu() {
	$slug = add_submenu_page(
		'settings.php',
		__( 'QRCodes', 'qrcodes' ),
		__( 'QRCodes', 'qrcodes' ),
		'manage_network_options',
		'qrcodes-network',
		'qrcodes_admin_network_page'
	);
	add_action( "load-{$slug}", 'qrcodes_admin_network_load' );
}
add_action( 'network_admin_menu', 'qrcodes_admin_network_menu' );

function qrcodes_admin_network_load() {
	add_action(
		'network_admin_notices',
		'qrcodes_admin_network_notices'
	);
	if ( wp_is_large_network() ) {
		add_action(
			'network_admin_notices',
			'qrcodes_network_notice_generate_large_network'
		);
	}
}

function qrcodes_admin_network_notices() {
    settings_errors( 'qrcodes-network-generate' );
}

function qrcodes_admin_network_page() {
	?><div class="wrap"><?php
		screen_icon();
		?><h2><?php
			_e( 'QRCodes network settings', 'qrcodes' );
		?></h2>
		<form
			method="post"
			action="<?php echo admin_url( 'options.php' ); ?>"
		><?php
			settings_fields( 'qrcodes-network-group' );
			do_settings_sections( 'qrcodes-network' );
			submit_button();
		?></form>
	</div><?php
}

function qrcodes_network_section() {
	?><p><?php
		_e( 'Set network options used by QRCodes plugin.', 'qrcodes' );
	?></p><?php
}

function qrcodes_admin_network_init() {
	add_settings_section(
		'qrcodes-network',
		__( 'General settings', 'qrcodes' ),
		'qrcodes_network_section',
		'qrcodes-network'
	);

	register_setting(
		'qrcodes-network-group',
		'qrcodes-network-generate',
		'qrcodes_save_settings_network_generate'
	);
	add_settings_field(
		'qrcodes-network-generate',
		__( 'Generate qrcodes for all sites', 'qrcodes' ),
		'qrcodes_display_network_generate',
		'qrcodes-network',
		'qrcodes-network',
		'qrcodes-network-generate'
	);

	register_setting(
		'qrcodes-network-group',
		'qrcodes-network-media-query',
		'qrcodes_save_settings_network_media_query'
	);
	add_settings_field(
		'qrcodes-network-media-query',
		__( 'Media query', 'qrcodes' ) .
		'<p>' .
			__( 'Split media query with a single comma.', 'qrcodes' ) .
		'</p>',
		'qrcodes_display_network_media_query',
		'qrcodes-network',
		'qrcodes-network',
		'qrcodes-network-media-query'
	);

	add_settings_field(
		'qrcodes-network-override-data-value',
		__( 'Default qrcodes\' link', 'qrcodes' ),
		'qrcodes_display_network_override_data_value',
		'qrcodes-network',
		'qrcodes-network',
		'qrcodes-network-override-data-value'
	);
	register_setting(
		'qrcodes-network-group',
		'qrcodes-network-override-data-value'
	);

	add_settings_field(
		'qrcodes-network-override-data-allow',
		__( 'Allow sites to override', 'qrcodes' ),
		'qrcodes_display_network_override_data_allow',
		'qrcodes-network',
		'qrcodes-network',
		'qrcodes-network-override-data-allow'
	);
	register_setting(
		'qrcodes-network-group',
		'qrcodes-network-override-data-allow',
		'wp_validate_boolean'
	);
}
add_action( 'admin_init', 'qrcodes_admin_network_init' );

function qrcodes_network_notice_generate_large_network() {
	?><div class="info"><p><?php
		_e(
			'You seem to have a <b>large network</b>. Qrcodes generation does not work yet.',
			'qrcodes'
		);
	?></p></div><?php
}

function qrcodes_save_settings_network_generate( $value = false ) {
	if ( ! $value ) {
		return get_option( 'qrcodes-network-generate' );
	}
	if ( wp_is_large_network() ) {
		add_settings_error(
			'qrcodes-network-generate',
			'no-generated',
			__(
				'You seem to have a <b>large network</b>. Qrcodes generation does not work yet.',
				'qrcodes'
			),
			'updated'
		);
		return get_option( 'qrcodes-network-generate' );
	}

	$offset = 0;

	for ( ;; ) {
		$sites = wp_get_sites( array(
			'spam'    => false,
			'offset'  => $offset,
			'deleted' => false,
		) );
		$nb = count( $sites );
		if ( $nb == 0 ) {
			break;
		}
		$offset += $nb;
		foreach ( $sites as $site ) {
			$id = $site['blog_id'];
			qrcodes_new_blog( $id );
			update_option( $id, 'qrcodes-generated', true );
		}
	}

	if ( $offset > 0 ) {
		add_settings_error(
			'qrcodes-network-generate',
			'no-generated',
			sprintf( _n(
				'All qrcodes has been generated for 1 site.',
				'All qrcodes has been generated for %d sites.',
				$offset,
				'qrcodes'
			), $offset ),
			'updated'
		);
	} else {
		add_settings_error(
			'qrcodes-network-generate',
			'no-generated',
			__( 'No qrcodes generated.', 'qrcodes' )
		);
	}
	return time();
}

function qrcodes_display_network_generate( $name ) {
	$opt = array();
	if ( wp_is_large_network() ) {
		$opt['disabled'] = 'disabled';
	}
	submit_button(
		__( 'Generate qrcodes', 'qrcodes' ),
		'large',
		$name,
		true,
		$opt
	);
	$last = get_option( $name );
	if ( $last ) {
		?><p><?php
			printf(
				__( 'Last generation: %s', 'qrcodes' ),
				date_i18n( get_option( 'date_format' ), $last )
			);
		?></p><?php
	}
}

function qrcodes_save_settings_network_media_query( $media ) {
	var_dump( $media );
	return array_unique( array_map( 'trim', explode( ',', $media ) ) );
}

function qrcodes_display_network_media_query( $name ) {
	$values = get_option( $name, array( 'print' ) ); ?>
	<input
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( implode( ',', $values ) ); ?>"
	/><?php
}

function qrcodes_display_network_override_data_allow( $name ) {
	$value = get_option( $name, true ); ?>
	<input
		type="checkbox"
		name="<?php echo esc_attr( $name ); ?>"
		<?php checked( $value ); ?>
	/><?php
}

function qrcodes_display_network_override_data_value( $name ) {
	$value = get_option( $name, network_home_url() ); ?>
	<input
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
	/><?php
}
