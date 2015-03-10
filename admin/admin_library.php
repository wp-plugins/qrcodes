<?php
function qrcodes_admin_init_library() {
	add_settings_section(
		'qrcodes-library',
		__( 'QRCode Library settings', 'qrcodes' ),
		'qrcodes_section_library',
		'qrcodes-library'
	);

	add_settings_field(
		'qrcodes-library-correction-level',
		__( 'Correction level', 'qrcodes' ),
		'qrcodes_display_library_correction_level',
		'qrcodes-library',
		'qrcodes-library',
		'qrcodes-library-correction-level'
	);
	register_setting(
		'qrcodes-group',
		'qrcodes-library-correction-level',
		'qrcodes_sanitize_library_correction_level'
	);

	add_settings_field(
		'qrcodes-library-matrix-point-size',
		__( 'Matrix point size', 'qrcodes' ),
		'qrcodes_display_library_matrix_point_size',
		'qrcodes-library',
		'qrcodes-library',
		'qrcodes-library-matrix-point-size'
	);
	register_setting(
		'qrcodes-group',
		'qrcodes-library-matrix-point-size',
		'qrcodes_sanitize_library_matrix_point_size'
	);

	add_settings_field(
		'qrcodes-library-margin',
		__( 'Margin', 'qrcodes' ),
		'qrcodes_display_library_margin',
		'qrcodes-library',
		'qrcodes-library',
		'qrcodes-library-margin'
	);
	register_setting(
		'qrcodes-group',
		'qrcodes-library-margin',
		'absint'
	);
}
add_action( 'admin_init', 'qrcodes_admin_init_library' );

function qrcodes_library_admin_add_page( $pages ) {
	$pages['library'] = array(
		'menu'     => __( 'Library', 'qrcodes' ),
		'sections' => 'qrcodes-library',
	);
	return $pages;
}
add_filter( 'qrcodes-admin-page', 'qrcodes_library_admin_add_page' );

function qrcodes_section_library() {
	?><p><?php
		_e( 'Set options used by QRCode PHP library.', 'qrcodes' );
	?></p><?php
}

function qrcodes_sanitize_library_correction_level( $value ) {
	$values = array(
		QR_ECLEVEL_L,
		QR_ECLEVEL_M,
		QR_ECLEVEL_Q,
		QR_ECLEVEL_H
	);
	if ( ! in_array( $value, $values ) ) {
		return false;
	}
	return $value;
}

function qrcodes_display_library_correction_level( $name ) {
	$value = get_blog_option(
		get_current_blog_id(),
		$name,
		QR_ECLEVEL_M
	);
	?><select name="<?php echo esc_attr( $name ); ?>">
		<option
				<?php selected( $value, QR_ECLEVEL_L ); ?>
				value="<?php echo esc_attr( QR_ECLEVEL_L ); ?>"
			>
			<?php _e( 'Low', 'qrcodes' ); ?>
		</option>
		<option
				<?php selected( $value, QR_ECLEVEL_M ); ?>
				value="<?php echo esc_attr( QR_ECLEVEL_M ); ?>"
			>
			<?php _e( 'Medium', 'qrcodes' ); ?>
		</option>
		<option
				<?php selected( $value, QR_ECLEVEL_Q ); ?>
				value="<?php echo esc_attr( QR_ECLEVEL_Q ); ?>"
			>
			<?php _e( 'Normal', 'qrcodes' ); ?>
		</option>
		<option
				<?php selected( $value, QR_ECLEVEL_H ); ?>
				value="<?php echo esc_attr( QR_ECLEVEL_H ); ?>"
			>
			<?php _e( 'High', 'qrcodes' ); ?>
		</option>
	</select><?php
}

function qrcodes_sanitize_library_matrix_point_size( $value ) {
	$value = absint( $value );
	if ( 0 == $value ) {
		return false;
	}
	return $value ;
}

function qrcodes_display_library_matrix_point_size( $name ) {
	$value = get_blog_option( get_current_blog_id(), $name, 3 );
	?><input
		min="1"
		step="1"
		type="number"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
	/><?php
}

function qrcodes_display_library_margin( $name ) {
	$value = get_blog_option( get_current_blog_id(), $name, 4 );
	?><input
		min="0"
		step="1"
		type="number"
		name="<?php echo esc_attr( $name ); ?>"
		value="<?php echo esc_attr( $value ); ?>"
	/><?php
}
