/**
 * Selezione dell'immagine in evidenza tramite la Media Library di WordPress,
 * nella schermata "Gestisci The Wall".
 *
 * Questo script è caricato SOLO in wp-admin, in quella pagina. Il front-end del
 * tema resta senza JavaScript, come da preview approvata.
 */
( function ( $ ) {
	'use strict';

	var strings = window.onthewallArtworkManager || {};

	/**
	 * Apre la Media Library per un controllo immagine e ne aggiorna anteprima e
	 * campo nascosto quando l'utente seleziona un allegato.
	 *
	 * @param {jQuery} $button Pulsante "Scegli immagine" cliccato.
	 */
	function openFrame( $button ) {
		var target   = $button.data( 'target' );
		var $input   = $( '#' + target + '_input' );
		var $preview = $( '#' + target + '_preview' );
		var $remove  = $button.closest( '.onthewall-media-actions' ).find( '.onthewall-media-remove' );

		var frame = wp.media( {
			title: strings.frameTitle || '',
			button: { text: strings.frameButton || '' },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();
			var thumb      = ( attachment.sizes && attachment.sizes.thumbnail )
				? attachment.sizes.thumbnail.url
				: attachment.url;

			$input.val( attachment.id );
			$preview.attr( 'src', thumb ).removeClass( 'hidden' );
			$remove.removeClass( 'hidden' );
		} );

		frame.open();
	}

	/**
	 * Svuota il controllo immagine (rimuove l'associazione).
	 *
	 * @param {jQuery} $button Pulsante "Rimuovi" cliccato.
	 */
	function clearControl( $button ) {
		var target = $button.data( 'target' );

		$( '#' + target + '_input' ).val( '' );
		$( '#' + target + '_preview' ).attr( 'src', '' ).addClass( 'hidden' );
		$button.addClass( 'hidden' );
	}

	$( function () {
		$( document ).on( 'click', '.onthewall-media-button', function ( event ) {
			event.preventDefault();
			openFrame( $( this ) );
		} );

		$( document ).on( 'click', '.onthewall-media-remove', function ( event ) {
			event.preventDefault();
			clearControl( $( this ) );
		} );
	} );
}( jQuery ) );
