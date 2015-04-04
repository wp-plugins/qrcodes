<?php
$list = false;

function qrcodes_index_admin_page() {
	global $list;

	?><div class="wrap"><?php
		?><h2><?php
			_e( 'QRCodes list', 'qrcodes' );
		?></h2>
		<form
			method="post"
			action="?page=qrcodes-index"
		><?php
			$list->search_box( __( 'Filter', 'qrcodes' ), 'data' );
		?></form>
		<div id="poststuff">
			<div id="post-body">
				<div
					id="qrcodes-index-page"
					class="postbox-container"
				>
					<form
						class="postbox"
						method="post"
						action="?page=qrcodes-index"
					><?php
						settings_fields( 'qrcodes-index-group' );
						do_settings_sections( 'qrcodes-index' );
						$list->display();
					?></form>
				</div>
			</div>
		</div>
	</div><?php
}

function qrcodes_index_delete_blog( $blog_id ) {
	$qrcodes = get_blog_option(
		$blog_id,
		'qrcodes-index-generated',
		array()
	);

	$error = false;
	foreach ( $qrcodes as $qrcode ) {
		if ( ! qrcodes_remove( $qrcode['data'], $blog_id ) ) {
			$error = true;
		}
	}

	if ( ! $error ) {
		delete_blog_option( $blog_id, 'qrcodes-index-generated' );
	}
}
add_action( 'delete_blog', 'qrcodes_index_delete_blog' );

function qrcodes_index_delete_post( $id ) {
	qrcodes_remove( get_permalink( $id ) );
}
add_action( 'delete_post', 'qrcodes_index_delete_post' );

function qrcodes_index_admin_menu() {
	$slug = add_options_page(
		__( 'QRCodes list', 'qrcodes' ),
		__( 'QRCodes list', 'qrcodes' ),
		'manage_options',
		'qrcodes-index',
		'qrcodes_index_admin_page'
	);
	add_action( "load-{$slug}", 'qrcodes_index_admin_load' );
}
add_action( 'admin_menu', 'qrcodes_index_admin_menu' );

function qrcodes_index_admin_load() {
	add_action( 'admin_notices', 'qrcodes_index_admin_notices' );
}

function qrcodes_index_admin_notices() {
	settings_errors( 'qrcodes-index-list' );
}

function qrcodes_index_admin_section() {
	?><p><?php
		_e( 'All qrcodes stored for your blog.', 'qrcodes' );
	?></p><?php
}

function qrcodes_index_admin_init() {
	add_settings_section(
		'qrcodes-index',
		__( 'List', 'qrcodes' ),
		'qrcodes_index_admin_section',
		'qrcodes-index'
	);
	register_setting(
		'qrcodes-index-group',
		'qrcodes-index-list'
	);

	global $list;

	$list = new Qrcodes_Index_List_Table(
		array(
			'save_page'        => 'options-general.php',
			'option_name'      => 'qrcodes-index-generated',
			'args_save_page'   => array(
				'page'             => $_REQUEST['page'],
				'_wp_http_referer' => $_SERVER['REQUEST_URI'],
			),
			'suffix_save_page' => '',
		)
	);
}
add_action( 'admin_init', 'qrcodes_index_admin_init' );

function qrcodes_index_admin_current_screen( $current_screen ) {
	if ( 'settings_page_qrcodes-index' == $current_screen->id ) {
		global $list;

		$list->prepare();
		if (
				isset( $_REQUEST['page'] ) &&
				'qrcodes-index' == $_REQUEST['page'] &&
				isset( $_REQUEST['_wpnonce'] ) &&
				check_admin_referer( 'bulk-qrcodes' )
			) {
			if ( $list->process_bulk_action() ) {
				$url = wp_get_referer();
				if ( $url ) {
					wp_safe_redirect( $url, 302 );
				} else {
					wp_safe_redirect( '?page=qrcodes-index', 302 );
				}
				die();
			}
		}
	}
}
add_action( 'current_screen', 'qrcodes_index_admin_current_screen' );

function qrcodes_index_generate( $blog_id, $data, $path ) {
	$qrcodes = get_blog_option(
		$blog_id,
		'qrcodes-index-generated',
		array()
	);
	$qrcodes[ $data ] = array(
		'path' => $path,
		'time' => current_time( 'timestamp' ),
	);
	update_blog_option( $blog_id, 'qrcodes-index-generated', $qrcodes );
}
add_action( 'qrcodes-generate', 'qrcodes_index_generate', 10, 3 );

function qrcodes_index_remove( $blog_id, $data ) {
	$qrcodes = get_blog_option(
		$blog_id,
		'qrcodes-index-generated',
		array()
	);
	unset( $qrcodes[ $data ] );
	update_blog_option( $blog_id, 'qrcodes-index-generated', $qrcodes );
}
add_action( 'qrcodes-remove', 'qrcodes_index_remove' );

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once path_join(
		ABSPATH,
		'wp-admin/includes/class-wp-list-table.php'
	);
}

class Qrcodes_Index_List_Table extends WP_List_Table {
	public
		$save_page,
		$option_name,
		$args_save_page,
		$suffix_save_page;

	function __construct( $options ) {
		parent::__construct(
			array(
				'singular' => 'qrcode',
				'plural'   => 'qrcodes',
				'ajax'     => false,
			)
		);
		$this->save_page        = $options['save_page'];
		$this->option_name      = $options['option_name'];
		$this->args_save_page   = $options['args_save_page'];
		$this->suffix_save_page = $options['suffix_save_page'];
		$this->nonce = wp_create_nonce(
			'bulk-' . $this->_args['plural']
		);
	}

	function get_columns() {
		return array(
			'cb'      => '<input type="checkbox" />',
			'preview' => __( 'Preview', 'qrcodes' ),
			'path'    => __( 'Path', 'qrcodes' ),
			'data'    => __( 'Data', 'qrcodes' ),
			'time'    => __( 'Creation date', 'qrcodes' ),
		);
	}

	public function get_sortable_columns() {
		return array(
			'path' => array( 'path', true ),
			'data' => array( 'data', false ),
			'time' => array( 'time', false ),
		);
	}

	function extra_tablenav( $which ) {
		switch ( $which ) {
			case 'top':
				break;
			case 'bottom':
				break;
			default:
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

	function prepare() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$items = get_blog_option(
			get_current_blog_id(),
			$this->option_name,
			array()
		);

		$this->prepare_items( $items );
	}

	function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'qrcodes' ),
		);
	}

	function filter_items( $items ) {
		if ( ! empty( $_REQUEST['s'] ) ) {
			$filter = addslashes( $_REQUEST['s'] );
			$items  = array_filter(
				$items,
				create_function(
					'$item',
					'$value = stripos( $item["data"], "' . $filter . '" );' .
					'return false !== $value;'
				)
			);
		}
		return $items;
	}

	function delete_action() {
		if ( empty( $_REQUEST[ $this->option_name ] ) ) {
			add_settings_error(
				'qrcodes-index-list',
				'remove-empty',
				__( 'No qrcodes selected.', 'qrcodes' ),
				'update-nag'
			);
			return;
		}

		$items = get_blog_option(
			get_current_blog_id(),
			$this->option_name,
			array()
		);
		$items_to_delete = $_REQUEST[ $this->option_name ];
		$error = array();
		foreach ( $items_to_delete as &$item ) {
			$item = (string) $item;
		}
		foreach ( $items_to_delete as $item ) {
			if ( ! isset( $items[ $item ] ) ) {
				$error[] = $item;
			}
		}
		$nb_error = count( $error );
		if ( $nb_error > 0 ) {
			add_settings_error(
				'qrcodes-index-list',
				'remove-no-exists',
				_n(
					'This qrcode does not exists.',
					'These qrcodes do not exists.',
					$nb_error,
					'qrcodes'
				),
				'error'
			);
			return;
		}

		$blog_id = get_current_blog_id();
		$error   = array();
		foreach ( $items_to_delete as $item ) {
			if ( ! qrcodes_exists( $item ) ) {
				qrcodes_index_remove( $blog_id, $item );
			} else if ( ! qrcodes_remove( $item ) ) {
				$error[] = $item;
			}
		}

		$nb_error = count( $error );
		if ( $nb_error > 0 ) {
			add_settings_error(
				'qrcodes-index-list',
				'remove-no-exists',
				_n(
					'Cannot remove this qrcode.',
					'Cannot remove these qrcodes.',
					$nb_error,
					'qrcodes'
				),
				'error'
			);
			return;
		}

		$size = count( $items_to_delete );
		add_settings_error(
			'qrcodes-index-list',
			'removed',
			sprintf(
				_n(
					'1 qrcode removed.',
					'%d qrcodes removed.',
					$size,
					'qrcodes'
				),
				$size
			),
			'updated'
		);

		$this->prepare_items( $items );
	}

	function process_bulk_action() {
		$action = $this->current_action();

		switch ( $action ) {
			case 'delete':
				$this->delete_action();
				return true;
			default:
				return false;
		}
	}

	function sanitize_order( $order = null ) {
		if ( 'desc' != $order ) {
			$order = 'asc';
		}
		return $order;
	}

	function sanitize_orderby( $orderby = null ) {
		if ( ! in_array(
			$orderby,
			array_keys( $this->get_sortable_columns() )
		) ) {
			$orderby = 'path';
		}
		return $orderby;
	}

	function get_sort_function() {
		$order   = $this->sanitize_order(   @$_REQUEST['order']   );
		$orderby = $this->sanitize_orderby( @$_REQUEST['orderby'] );

		$args = $order == 'asc' ?
			'$qrcode1,$qrcode2' :
			'$qrcode2,$qrcode1';
		$orderby = addslashes( $orderby );
		$func_content  = '$qrcode1 = $qrcode1[\'' . $orderby . '\']';
		$func_content .= '$qrcode2 = $qrcode2[\'' . $orderby . '\']';
		switch ( $orderby ) {
			case 'time':
				$func_content .= 'return $qrcode1 - $qrcode2;';
				break;
			default:
				$func_content .= 'return strcmp( $qrcode1, $qrcode2 );';
				break;
		}
		return create_function(
			$args,
			$func_content
		);
	}

	function prepare_items( $qrcodes ) {
		$func  = $this->get_sort_function();
		$items = array();

		foreach ( $qrcodes as $data => $qrcode ) {
			$items[] = array(
				'data' => $data,
				'path' => $qrcode['path'],
				'time' => $qrcode['time'],
			);
		}

		$items       = $this->filter_items( $items );
		$per_page    = 10;
		$total_items = count( $items );
		$total_pages = ceil( $total_items / $per_page );

		$this->set_pagination_args(
			array(
				'per_page'    => $per_page,
				'total_items' => $total_items,
				'total_pages' => $total_pages,
			)
		);

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
			esc_attr( $item['data'] )
		);
	}

	function column_path( $item, $column_name ) {
		$this->screen;
		$query = array(
			'_wpnonce'         => $this->nonce,
			$this->option_name => array( $item['data'] ),
		);

		$actions = array();
		foreach ( $this->get_bulk_actions() as $action => $title ) {
			$actions[ $action ] = '<a href="' . $this->save_page . '?' .
					http_build_query(
						array_merge(
							$query,
							$this->args_save_page,
							array( 'action' => $action )
						)
					) .
					$this->suffix_save_page .
				'">' .
					esc_html( strtolower( $title ) ) .
				'</a>';
		}

		echo
			esc_html( $item['path'] ) .
			$this->row_actions( $actions );
	}

	function column_preview( $item, $column_name ) {
		echo '<a target="_blank" href="' . esc_url( qrcodes_get_url( $item['data'] ) ) . '">' .
				'<img class="qrcodes" src="' . esc_url( qrcodes_get_url( $item['data'] ) ) . '" />' .
			'</a>';
	}

	function column_time( $item, $column_name ) {
		echo
			esc_html( date_i18n( get_option( 'date_format' ), $item['time'] ) ) .
			'<br />' .
			esc_html( date_i18n( get_option( 'time_format' ), $item['time'] ) );
	}

	function column_default( $item, $column_name ) {
		echo esc_html( $item[ $column_name ] );
	}
}
