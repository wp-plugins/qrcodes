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
		?><h2><?php
			_e( 'QRCodes network settings', 'qrcodes' );
		?></h2>
		<form
			method="post"
			action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>"
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
	// General section
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

	$total  = 0;
	$offset = 0;
	for ( ;; ) {
		$sites = wp_get_sites(
			array(
				'spam'    => false,
				'offset'  => $offset,
				'deleted' => false,
			)
		);
		$nb = count( $sites );
		if ( 0 == $nb ) {
			break;
		}
		remove_shortcode( 'user-id' );
		add_shortcode(
			'user-id',
			create_function( '$atts', 'return \'0\';' )
		);
		$offset += $nb;
		foreach ( $sites as $site ) {
			$site_id = $site['blog_id'];

			// Generate generic qrcodes
			qrcodes_new_blog( $site_id );
			update_option( $site_id, 'qrcodes-generated', true );

			// Generate qrcode for each post of the site
			switch_to_blog( $site_id );
			$offset_post = 0;
			for ( ;; ) {
				$posts = get_posts(
					array(
						'offset'      => $offset_post,
						'post_type'   => array( 'any' ),
						'post_status' => array(
							'publish',
							'private',
						),
					)
				);
				$nb = count( $posts );
				if ( 0 == $nb ) {
					break;
				}

				$offset_post += $nb ;
				foreach ( $posts as $post ) {
					$url = get_permalink( $post->ID );
					remove_shortcode( 'current-url' );
					add_shortcode(
						'current-url',
						create_function(
							'$atts',
							'$atts   = shortcode_atts(
								array( \'encode\' => \'false\' ),
								$atts,
								\'current-url\'
							);
							$encode = $atts[\'encode\'];
							$url    = \'' . addslashes( $url ) . '\';
							if ( wp_validate_boolean( $encode ) ) {
								$url = urlencode( $url );
							}
							return $url;'
						)
					);
					qrcodes_generate( $url );
				}
			}
			$total += $offset_post;
		}
	}
	restore_current_blog();
	$total_site = $offset;
	$total     += $offset;

	switch ( $total ) {
		case 0:
			add_settings_error(
				'qrcodes-network-generate',
				'no-generated',
				__( 'No qrcodes generated.', 'qrcodes' )
			);
			break;
		case 1:
			add_settings_error(
				'qrcodes-network-generate',
				'no-generated',
				sprintf(
					_n(
						'1 qrcode has been generated for 1 site.',
						'1 qrcode has been generated for %d sites.',
						$total_site,
						'qrcodes'
					),
					$total,
					$total_site
				),
				'updated'
			);
			break;
		default;
			add_settings_error(
				'qrcodes-network-generate',
				'no-generated',
				sprintf(
					_n(
						'%d qrcodes has been generated for 1 site.',
						'%d qrcodes has been generated for %d sites.',
						$total_site,
						'qrcodes'
					),
					$total,
					$total_site
				),
				'updated'
			);
			break;
	}
	return current_time( 'timestamp' );
}

function qrcodes_display_network_generate( $name ) {
	if ( wp_is_large_network() ) {
		submit_button(
			__( 'Generate qrcodes', 'qrcodes' ),
			'primary',
			$name,
			true,
			array( 'disabled' => 'disabled' )
		);
	} else {
		submit_button(
			__( 'Generate qrcodes', 'qrcodes' ),
			'primary',
			$name
		);
	}
	$last = get_option( $name );

	if ( $last ) {
		?><p><?php
			printf(
				__( 'Last generation: %s ago.', 'qrcodes' ),
				human_time_diff( $last, current_time( 'timestamp' ) )
			);
		?></p><?php
	}
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
	$home  = network_home_url();
	$value = get_option( $name, $home ); ?>
	<input
		type="text"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
		placeholder="<?php echo esc_attr( $home ); ?>"
		autocomplete="off"
	/><?php
}
