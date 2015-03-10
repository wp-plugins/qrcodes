<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
   require_once path_join( ABSPATH, 'wp-admin/includes/class-wp-list-table.php' );
}

class Qrcodes_Media_Query_List_Table extends WP_List_Table {
	public $option_name, $save_page, $option_group;

	function __construct( $options ) {
		parent::__construct( array(
			'singular' => 'medium',
			'plural'   => 'media',
			'ajax'     => false,
		) );
		$this->save_page   = $options['save_page'];
		$this->option_name = $options['option_name'];
		$this->option_group = $options['option_group'];
		$this->nonce = wp_create_nonce(
			'bulk-' . $this->_args['plural']
		);
	}

	function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'name'        => __( 'Name', 'qrcodes' ),
			'description' => __( 'Description', 'qrcodes' ),
		);
	}

	public function get_sortable_columns() {
		return array(
			'name' => array( 'name', true ),
		);
	}
	
	function extra_tablenav( $which ) {
		switch ( $which ) {
			case 'top':
				settings_errors( 'qrcodes-media-query-list' );
				break;
			case 'bottom':
				break;
			default:
				print_r( $which );
				break;
		}
	}
	
	function display_tablenav( $which ) {
		if ( 'top' == $which ) {
			?><input
				type="hidden"
				name="_wpnonce"
				value="<?php echo esc_attr( $this->nonce ); ?>"
			/><?php
			wp_referer_field();
		}

		?><div class="tablenav <?php echo esc_attr( $which ); ?>">
			<div class="alignleft actions bulkactions"><?php
				$this->bulk_actions( $which );
			?></div><?php
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?><br class="clear" />
		</div><?php
	}
	
	function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'qrcodes' ),
		);
	}

	function process_bulk_action() {
		if ( 'delete' === $this->current_action() ) {
			$media = get_option( $this->option_name, array() );

			if ( empty( $_REQUEST[ $this->option_name ] ) ) {
				add_settings_error(
					'qrcodes-media-query-list',
					'remove-empty',
					__( 'No media query selected.', 'qrcodes' ),
					'update-nag'
				);
				return;
			}

			$items = $media;
			$media_to_delete = $_REQUEST[ $this->option_name ];
			$error = array();
			foreach ( $media_to_delete as $medium ) {
				if ( isset( $media[ $medium ] ) ) {
					qrcodes_media_query_remove_options( $medium );
					unset( $media[ $medium ] );
				} else {
					$error[] = $medium;
				}
			}
			$nb_error = count( $error );
			if ( $nb_error > 0 ) {
				add_settings_error(
					'qrcodes-media-query-list',
					'remove-no-exists',
					_n(
						'This medium does not exists.',
						'These media do not exists.',
						$nb_error,
						'qrcodes'
					) . '<br />' .
					implode( __( ',', 'qrcodes' ), $error ),
					'error'
				);
				return;
			}

			$done = update_option(
				$this->option_name,
				$media
			);
			if ( ! $done ) {
				add_settings_error(
					'qrcodes-media-query-list',
					'remove-error',
					__( 'Apologies, cannot save changes.', 'qrcodes' ),
					'error'
				);
			die('non');
				return;
			}

			$size = count( $_REQUEST['media'] );
			add_settings_error(
				'qrcodes-media-query-list',
				'removed',
				sprintf(
					_n(
						'1 medium removed',
						'%d media removed.',
						$size,
						'qrcodes'
					),
					$size
				),
				'updated'
			);

			$this->prepare_items( $media );
		}
	}

	function prepare() {
		$this->_column_headers = array( 
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);
	
		$this->prepare_items( get_option( $this->option_name, array() ) );
	}

	function prepare_items( $media ) {
		if (
				empty( $_REQUEST[ 'order' ] ) ||
				$_REQUEST[ 'order' ] != 'desc' ||
				empty( $_REQUEST['orderby'] ) ||
				$_REQUEST['orderby'] != 'name'
			) {
			$func = create_function(
				'$medium1,$medium2',
				'return strcmp( $medium1["ID"], $medium2["ID"] );'
			);
		} else {
			$func = create_function(
				'$medium1,$medium2',
				'return strcmp( $medium2["ID"], $medium1["ID"] );'
			);
		}

		$per_page = 5;

		$items = array();
		foreach ( $media as $medium => $desc ) {
			$items[] = array(
				'ID'          => $medium,
				'description' => $desc,
			);
		}
		$total_items = count( $items );
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args( array(
			'per_page'    => $per_page,
			'total_items' => $total_items,
			'total_pages' => $total_pages,
		) );

		$paged = $this->get_pagenum();

		usort( $items, $func );
		$this->items = array_slice(
			$items,
			( $paged - 1 ) * $per_page,
			$per_page,
			true
		);
	}

    function column_cb( $item ){
        return sprintf(
            '<input type="checkbox" name="%1$s[]" value="%2$s" />',
            esc_attr( $this->option_name ),
            esc_attr( $item['ID'] )
        );
    }

	function column_name( $item, $column_name ) {
		$this->screen;
		$query = http_build_query( array(
			'action'           => 'delete',
			'_wpnonce'         => $this->nonce,
			'_wp_http_referer' => $_SERVER['REQUEST_URI'],
			$this->option_name => array( $item['ID'] ),
		) );
        $actions = array(
            'delete'    => sprintf(
				'<a href="%1$s%2$s">%3$s</a>',
				$this->save_page,
				$query,
				__( 'delete', 'qrcodes' )
			),
        );
		echo
			esc_html( $item['ID'] ) .
			$this->row_actions( $actions );
	}

	function column_default( $item, $column_name ) {
		echo $item[ $column_name ];
	}
}
