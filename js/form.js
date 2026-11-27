jQuery( function( $ ) {

	$( '.unomoon_form input[data-conv-half-alphanumeric="true"]' ).on( 'change', function() {
		var txt  = $( this ).val();
		var half = txt.replace( /[Ａ-Ｚａ-ｚ０-９]/g, function( s ) {
			return String.fromCharCode( s.charCodeAt( 0 ) - 0xFEE0 )
		} );
		$( this ).val( half );
	} );

	var file_delete = $( '.unomoon_form .unomoonform-file-delete' );
	file_delete.each( function( i, e ) {
		var target = $( e ).data( 'unomoonform-file-delete' );
		var hidden_field = $( 'input[type="hidden"][name="' + target + '"]' );
		if ( hidden_field.val() ) {
			$( e ).css( 'visibility', 'visible' );
		}
		$( e ).on( 'click', function() {
			var file_field = $( 'input[type="file"][name="' + target + '"]' );
			var new_field = $( file_field[0].outerHTML );
			$( this ).css( 'visibility', 'hidden' );
			file_field.replaceWith( new_field );

			hidden_field.parent().fadeOut( 100, function() {
				$( this ).remove();
			} );
		} );
	} );
	$( document ).on( 'change', '.unomoon_form input[type="file"]', function() {
		var name = $( this ).attr( 'name' );
		file_delete.closest( '[data-unomoonform-file-delete="' + name + '"]' ).css( 'visibility', 'visible' );
	} );

	var unomoon_form_button_no_click = true;
	$( '.unomoon_form input[type="submit"]' ).on( 'click', function() {
		var formElement = $( this ).closest( 'form' )[0];
		if ( formElement && formElement.checkValidity && !formElement.checkValidity() ) {
			return;
		}
		if ( unomoon_form_button_no_click ) {
			unomoon_form_button_no_click = false;
		} else {
			$( this ).prop( 'disabled', true );
		}
	} );
} );
