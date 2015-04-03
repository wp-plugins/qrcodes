jQuery( document ).ready( function ( $ ) {
	$( ".postbox" )
		.each( function ( index, elem ) {
			var page = $( elem );
			page
				.children( "h3" )
					.each( function ( index, title ) {
						var title = $( title );
						title
							.html(
								$( "<span></span>" )
									.text(
										title
											.text()
									)
							)
							.remove();
		
						var inside = $( "<div></div>" )
							.append( title )
							.addClass( "inside" );
						page
							.contents()
								.not( ".inside,h3,h3 ~ *" )
									.appendTo( inside );
		
						page
							.append( inside );
					} );
			page
				.children( ".inside" )
					.each( function ( index, elem ) {
						$( elem )
							.before(
								$( elem )
									.children( "h3" )
							);
					} );
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
	$( ".nav-tab-wrapper .nav-tab" )
		.first()
			.click();
} );
jQuery( document ).ready( function ( $ ) {
	$( qrcodesPointerMouseOver ).each( function ( index, elem ) {
		$( elem.selector )
			.mouseenter( function() {
				$( ".qrcodes-pointer-mouseover-open" )
					.pointer( "close" );
				$( this )
					.pointer( "open" );
			} )
			.pointer( {
				content:
					"<h3>" + elem.title + "</h3>" +
					elem.content,
				position: {
					edge:  elem.edge,
					align: elem.align
				},
				open: function () {
					$( this )
						.addClass( "qrcodes-pointer-mouseover-open" );
				},
				close: function () {
					$( this )
						.removeClass( "qrcodes-pointer-mouseover-open" );
				}
			} );
	} );
} );