jQuery( document ).ready( function ( $ ) {
	$( ".postbox" )
		.each( function ( index, elem ) {
			var title = $( elem )
				.children( "h3" );
			title
				.html(
					$( "<span></span>" )
						.text(
							title
								.text()
						)
				);
			
			var inside = $( "<div></div>" )
				.addClass( "inside" );
			$( elem )
				.contents().not( title )
					.appendTo( inside );
			$( elem )
				.append( inside );
		} );
	var container = $( "#post-body .postbox-container" );
	$( ".nav-tab-wrapper .nav-tab" ).click( function() {
		$( ".nav-tab-active" )
			.removeClass( "nav-tab-active" );
		$( this )
			.addClass( "nav-tab-active" );
		var page = $( $( this ).attr( "href" ) ).show();
		container
			.not( page )
				.hide();
		return false;
	} );
	$( ".nav-tab-wrapper .nav-tab" ).first().click();
} );
jQuery( document ).ready( function ( $ ) {
	$( qrcodesPointerMouseOver ).each( function ( index, elem ) {
		$( elem.selector )
			.mouseenter( function() {
				$( this ).pointer( 'open' );
			} )
			.pointer( {
				content:
					'<h3>' + elem.title + '</h3>' +
					elem.content,
				position: {
					edge:  elem.edge,
					align: elem.align
				},
				stop: function () {}
			} );
	} );
} );