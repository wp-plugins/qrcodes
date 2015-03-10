jQuery( document ).ready( function ( $ ) {
	var title = $( "#qrcodes-new-medium" )
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
	$( "#qrcodes-new-medium" )
		.contents().not( title )
			.appendTo( inside );
	$( "#qrcodes-new-medium" )
		.append( inside );
} );
