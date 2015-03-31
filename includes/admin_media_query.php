<?php
define(
	'QRCODES_MEDIA_QUERY_OPTION_NAME',
	'qrcodes-media-query-network'
);

$media_list = false;

function qrcodes_media_query_admin_network_menu() {
	$slug = add_submenu_page(
		'settings.php',
		__( 'Media query', 'qrcodes' ),
		__( 'Media query', 'qrcodes' ),
		'manage_network_options',
		'qrcodes-media-query-network-page',
		'qrcodes_media_query_admin_network_page'
	);
	add_action(
		"load-{$slug}",
		'qrcodes_media_query_admin_network_load_media_query',
		12
	);
}
if ( is_multisite() ) {
	add_action(
		'network_admin_menu',
		'qrcodes_media_query_admin_network_menu'
	);
} else {
	add_action(
		'admin_menu',
		'qrcodes_media_query_admin_network_menu'
	);
}

function qrcodes_media_query_admin_network_load_media_query() {
	add_action(
		'network_admin_notices',
		'qrcodes_media_query_admin_network_notices'
	);
}

function qrcodes_media_query_admin_network_notices() {
    settings_errors( QRCODES_MEDIA_QUERY_OPTION_NAME );
}

function qrcodes_media_query_admin_network_page() {
	global $media_list;

	?><div class="wrap">
		<h2><?php
			_e( 'Media query settings', 'qrcodes' );
		?></h2>
		<div id="poststuff">
			<div id="post-body">
				<div
					id="qrcodes-media-query-page"
					class="postbox-container"
				>
					<form
						class="postbox"
						method="post"
						action="?page=qrcodes-media-query-network-page"
					>
						<h3><span><?php
							_e( 'Media list', 'qrcodes' );
						?></span></h3>
						<div class="inside">
							<p><?php
								_e( 'Manage media query.', 'qrcodes' );
							?></p><?php
							$media_list->display();
						?></div>
					</form>
					<form
						id="qrcodes-new-medium"
						class="postbox"
						method="post"
						action="<?php echo admin_url( 'options.php' ); ?>"
					><?php
						settings_fields( 'qrcodes-media-query-network-new-medium-group' );
						do_settings_sections( 'qrcodes-media-query-network-page' );
					?></form>
				</div>
			</div>
		</div>
	</div><?php
}

function qrcodes_media_query_admin_script( $page ) {
	if ( 'settings_page_qrcodes-media-query-network-page' == $page ) {
		wp_enqueue_script(
			'qrcodes-admin',
			plugins_url(
				path_join( '/script', 'admin_network.js' ),
				QRCODES_INDEX_FILE
			),
			array( 'jquery' ),
			'0.1',
			true
		);
	}
}
add_action( 'admin_enqueue_scripts', 'qrcodes_media_query_admin_script' );

function qrcodes_media_query_admin_init_table() {
	global $media_list;

	require_once path_join(
		__DIR__,
		'media_query_list_table.php'
	);
	

	// Media list
	$media_list = new Qrcodes_Media_Query_List_Table( array(
		'save_page'   => '?page=qrcodes-media-query-network-page&',
		'option_name' => QRCODES_MEDIA_QUERY_OPTION_NAME,
	) );
}
add_action( 'admin_init', 'qrcodes_media_query_admin_init_table' );

function qrcodes_media_query_admin_current_screen( $current_screen ) {
	global $media_list;

	if ( 'settings_page_qrcodes-media-query-network-page-network' == $current_screen->id ) {
		$media_list->prepare();

		if (
				isset( $_REQUEST['page'] ) &&
				'qrcodes-media-query-network-page' == $_REQUEST['page'] &&
				isset( $_REQUEST['_wpnonce'] ) &&
				check_admin_referer( 'bulk-media' )
			) {
			$media_list->process_bulk_action();
			$url = wp_get_referer();
			if ( $url ) {
				wp_safe_redirect( $url, 302 );
			} else {
				wp_safe_redirect( '?page=qrcodes-media-query-network-page', 302 );
			}
			die();
		}
	}
}
add_action( 'current_screen', 'qrcodes_media_query_admin_current_screen' );

function qrcodes_media_query_admin_network_init() {
	// New medium
	add_settings_section(
		'qrcodes-media-query-network-new',
		__( 'New medium', 'qrcodes' ),
		'qrcodes_query_network_section_media_new',
		'qrcodes-media-query-network-page'
	);
	register_setting(
		'qrcodes-media-query-network-new-medium-group',
		QRCODES_MEDIA_QUERY_OPTION_NAME,
		'qrcodes_media_query_save_settings_network'
	);
	add_settings_field(
		'qrcodes-media-query-network-new-identifier',
		__( 'Identifier', 'qrcodes' ),
		'qrcodes_display_network_media_query_new_identifier',
		'qrcodes-media-query-network-page',
		'qrcodes-media-query-network-new',
		'qrcodes-media-query-network'
	);
	add_settings_field(
		'qrcodes-media-query-network-new-description',
		__( 'Description', 'qrcodes' ),
		'qrcodes_display_network_media_query_new_description',
		'qrcodes-media-query-network-page',
		'qrcodes-media-query-network-new',
		'qrcodes-media-query-network'
	);
	add_settings_field(
		'qrcodes-media-query-network-new-submit',
		'',
		'qrcodes_display_network_media_query_new_submit',
		'qrcodes-media-query-network-page',
		'qrcodes-media-query-network-new',
		'qrcodes-media-query-network'
	);
}
add_action( 'admin_init', 'qrcodes_media_query_admin_network_init' );

function qrcodes_media_query_section() {
	?><p><?php
		_e( 'Manage media query.', 'qrcodes' );
	?></p><?php
}

function qrcodes_media_query_admin_init() {
	$media_query = get_option(
		QRCODES_MEDIA_QUERY_OPTION_NAME,
		array()
	);
	foreach ( $media_query as $medium => $desc ) {
		$desc = addslashes( $desc );
		add_settings_section(
			"qrcodes-media-query-{$medium}",
			esc_html( $medium ),
			create_function( '', "echo esc_html( '{$desc}' );" ),
			'qrcodes-media-query'
		);

		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-active",
			'wp_validate_boolean'
		);
		add_settings_field(
			"qrcodes-media-query-{$medium}-active",
			__( 'Active', 'qrcodes' ),
			'qrcodes_display_media_query_active',
			'qrcodes-media-query',
			"qrcodes-media-query-{$medium}",
			"qrcodes-media-query-{$medium}-active"
		);

		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-horizontal-direction",
			'qrcodes_sanitize_media_query_horizontal_direction'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-horizontal-value",
			'qrcodes_media_query_save_css_value'
		);
		add_settings_field(
			"qrcodes-media-query-{$medium}-horizontal",
			__( 'Horizontal position', 'qrcodes' ),
			'qrcodes_display_media_query_horizontal',
			'qrcodes-media-query',
			"qrcodes-media-query-{$medium}",
			"qrcodes-media-query-{$medium}-horizontal"
		);

		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-vertical-direction",
			'qrcodes_sanitize_media_query_vertical_direction'
		);
		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-vertical-value",
			'qrcodes_media_query_save_css_value'
		);
		add_settings_field(
			"qrcodes-media-query-{$medium}-vertical",
			__( 'Vertical position', 'qrcodes' ),
			'qrcodes_display_media_query_vertical',
			'qrcodes-media-query',
			"qrcodes-media-query-{$medium}",
			"qrcodes-media-query-{$medium}-vertical"
		);

		register_setting(
			'qrcodes-group',
			"qrcodes-media-query-{$medium}-size",
			'qrcodes_media_query_save_css_value'
		);
		add_settings_field(
			"qrcodes-media-query-{$medium}-size",
			__( 'Size', 'qrcodes' ) . '<br />' .
			'<small>' .
				__( 'use carefully !', 'qrcodes' ) .
			'</small>',
			'qrcodes_media_query_display_size',
			'qrcodes-media-query',
			"qrcodes-media-query-{$medium}",
			"qrcodes-media-query-{$medium}-size"
		);
	}
}
add_action( 'admin_init', 'qrcodes_media_query_admin_init' );

function qrcodes_media_query_admin_add_page( $pages ) {
	$pages['media-query'] = array(
		'menu'     => __( 'Media query', 'qrcodes' ),
		'sections' => 'qrcodes-media-query',
	);
	return $pages;
}
add_filter( 'qrcodes-admin-page', 'qrcodes_media_query_admin_add_page' );

function qrcodes_query_network_section_media_new() {
	?><p><?php
		_e( 'Add and modify a media query.', 'qrcodes' );
	?></p><?php
}

function qrcodes_media_query_save_css_value( $size ) {
	if (
			isset( $size['auto'] ) &&
			wp_validate_boolean( $size['auto'] )
		) {
		return false;
	}
	$value = absint( $size['value'] );
	if ( false === $value ) {
		add_settings_error(
			'qrcodes-network-media-query',
			'invalid-value',
			__( 'Invalid value', 'qrcodes' ),
			'error'
		);
		return false;
	}
	$unit = $size['unit'];
	return $value . $unit;
}

function qrcodes_media_query_display_size( $name ) {
	$units = array(
		'%',
		'px',
	);
	$value = get_blog_option(
		get_current_blog_id(),
		$name,
		true
	); ?>
	<input
		id="<?php echo esc_attr( "{$name}[auto]" ); ?>"
		name="<?php echo esc_attr( "{$name}[auto]" ); ?>"
		type="checkbox"
		class="align-left"
		<?php checked( $value, false ); ?>
	/>
	<label for="<?php echo esc_attr( "{$name}[auto]" ); ?>">
		<?php _e( 'Automatic', 'qrcodes' ); ?>
	</label>
	<p class="more-options-inverted">
		<input
			id="<?php echo esc_attr( "{$name}[value]" ); ?>"
			min="0"
			step="1"
			type="number"
			name="<?php echo esc_attr( "{$name}[value]" ); ?>"
			value="<?php echo $value ? esc_attr( intval( $value ) ) : ''; ?>"
			placeholder="<?php esc_attr_e( 'Number', 'qrcodes' ); ?>"
		/>
		<select
			id="<?php echo esc_attr( "{$name}[unit]" ); ?>"
			name="<?php echo esc_attr( "{$name}[unit]" ); ?>"
		><?php
			foreach ( $units as $u ) {
				?><option
					value="<?php echo esc_attr( $u ); ?>"
					<?php selected( $u, substr( $value, -strlen( $u ) ) ); ?>
				><?php
					echo esc_html( $u );
				?></option><?php
			}
		?></select>
	</p><?php
}

function qrcodes_display_media_query_horizontal( $name ) {
	qrcodes_display_media_query_position( $name, array(
		'left'  => __( 'left', 'qrcodes' ),
		'right' => __( 'right', 'qrcodes' ),
	) );
}

function qrcodes_display_media_query_vertical( $name ) {
	qrcodes_display_media_query_position( $name, array(
		'top'    => __( 'top', 'qrcodes' ),
		'bottom' => __( 'bottom', 'qrcodes' ),
	) );
}

function qrcodes_display_media_query_position( $name, $directions ) {
	$units = array(
		'%',
		'px',
	);
	$dir = get_blog_option(
		get_current_blog_id(),
		"{$name}-direction",
		'right'
	);
	$value = get_blog_option(
		get_current_blog_id(),
		"{$name}-value",
		0
	); ?>
	<select
		id="<?php echo esc_attr( "{$name}-direction" ); ?>"
		name="<?php echo esc_attr( "{$name}-direction" ); ?>"
	><?php
		foreach ( $directions as $direction => $label ) {
			?><option
					value="<?php echo esc_attr( $direction ); ?>"
					<?php selected( $dir, $direction ); ?>
				><?php
				echo esc_html( $label );
			?></option><?php
		}
	?></select>
	<input
		min="0"
		step="1"
		type="number"
		name="<?php echo esc_attr( "{$name}-value[value]" ); ?>"
		value="<?php echo esc_attr( intval( $value ) ); ?>"
	/>
	<select
		id="<?php echo esc_attr( "{$name}-value[unit]" ); ?>"
		name="<?php echo esc_attr( "{$name}-value[unit]" ); ?>"
	><?php
		foreach ( $units as $unit ) {
			?><option
				value="<?php echo esc_attr( $unit ); ?>"
				<?php selected(
					$unit,
					substr( $value, - strlen( $unit ) )
				); ?>
			><?php
				echo esc_html( $unit );
			?></option><?php
		}
	?></select><?php
}

function qrcodes_display_media_query_active( $name ) {
	$active = get_blog_option(
		get_current_blog_id(),
		$name
	);
	?><input
		id="<?php echo esc_attr( $name ); ?>"
		name="<?php echo esc_attr( $name ); ?>"
		type="checkbox"
		<?php checked( $active ); ?> /><?php
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

function qrcodes_media_query_save_settings_network( $medium ) {
	$media = get_option( QRCODES_MEDIA_QUERY_OPTION_NAME, array() );
	if ( isset( $medium['add'] ) ) {
		if (
				empty( $medium['identifier'] ) ||
				! isset( $medium['description'] )
			) {
			add_settings_error(
				QRCODES_MEDIA_QUERY_OPTION_NAME,
				'invalid',
				__( 'Invalid new media query.', 'qrcodes' ),
				'error'
			);
			return $media;
		}
		add_settings_error(
			QRCODES_MEDIA_QUERY_OPTION_NAME,
			'saved',
			__( 'New media query saved.', 'qrcodes' ),
			'updated'
		);
		qrcodes_media_query_add_default_options( $medium['identifier'] );
		$media = array_merge(
			$media,
			array( $medium['identifier'] => $medium['description'] )
		);
	}
	return $media;
}

function qrcodes_display_network_media_query_new_identifier( $name ) {
	$media = array(
		'all',
		'aural',
		'braille',
		'embossed',
		'handheld',
		'print',
		'projection',
		'screen',
		'speech',
		'tty',
		'tv',
	);
	?><input
		id="<?php echo esc_attr( $name ); ?>-identifier"
		type="text"
		name="<?php echo esc_attr( $name ); ?>[identifier]"
		list="<?php echo esc_attr( $name ); ?>-identifier-list"
		placeholder="<?php esc_attr_e( 'identifier', 'qrcodes' ); ?>"
		autocomplete="off"
	/>
	<datalist id="<?php echo esc_attr( $name ); ?>-identifier-list"><?php
		foreach ( $media as $medium ) {
			?><option value="<?php echo esc_attr( $medium ); ?>" /><?php
		}
	?></datalist><?php
}

function qrcodes_display_network_media_query_new_description( $name ) {
	?><input
		id="<?php echo esc_attr( $name ); ?>-description"
		type="text"
		name="<?php echo esc_attr( $name ); ?>[description]"
		placeholder="<?php esc_attr_e( 'a short description', 'qrcodes' ); ?>"
		autocomplete="off"
	/><?php
}

function qrcodes_display_network_media_query_new_submit( $name ) {
	submit_button(
		__( 'Add new medium', 'qrcodes' ),
		'primary',
		"{$name}[add]"
	);
}
