/**
 * Immagini delle opere in bacheca: immagine principale e galleria, scelte dalla
 * Media Library o trascinate dal computer; miniature della galleria riordinabili.
 *
 * Questo script è caricato SOLO in wp-admin (Gestisci The Wall e modifica
 * dell'opera). Il front-end del tema resta senza JavaScript.
 */
( function ( $ ) {
	'use strict';

	var strings = window.onthewallArtworkManager || {};

	/**
	 * URL della miniatura di un allegato, sia da Media Library (sizes.*.url)
	 * sia da API REST (media_details.sizes.*.source_url).
	 *
	 * @param {Object} attachment Allegato.
	 * @return {string}
	 */
	function thumbUrl( attachment ) {
		var sizes = attachment.sizes || ( attachment.media_details && attachment.media_details.sizes );
		var thumb = sizes && sizes.thumbnail;

		if ( thumb ) {
			return thumb.url || thumb.source_url;
		}

		return attachment.url || attachment.source_url;
	}

	/**
	 * Imposta l'immagine principale di un controllo: campo nascosto, anteprima, "Rimuovi".
	 *
	 * @param {string} target Prefisso degli id del controllo.
	 * @param {number} id     ID dell'allegato.
	 * @param {string} thumb  URL della miniatura.
	 */
	function setMain( target, id, thumb ) {
		$( '#' + target + '_input' ).val( id );
		$( '#' + target + '_preview' ).attr( 'src', thumb ).removeClass( 'hidden' );
		$( '.onthewall-media-remove[data-target="' + target + '"]' ).removeClass( 'hidden' );
	}

	/**
	 * Apre la Media Library per l'immagine principale.
	 *
	 * @param {jQuery} $button Pulsante "Scegli immagine" cliccato.
	 */
	function openFrame( $button ) {
		var frame = wp.media( {
			title: strings.frameTitle || '',
			button: { text: strings.frameButton || '' },
			library: { type: 'image' },
			multiple: false
		} );

		frame.on( 'select', function () {
			var attachment = frame.state().get( 'selection' ).first().toJSON();

			setMain( $button.data( 'target' ), attachment.id, thumbUrl( attachment ) );
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

	/**
	 * Riscrive il campo nascosto della galleria dall'ordine delle miniature.
	 *
	 * @param {jQuery} $list Elenco <ul> delle miniature.
	 */
	function syncGallery( $list ) {
		var ids = $list.children( 'li' ).map( function () {
			return $( this ).data( 'id' );
		} ).get();

		$( '#' + $list.attr( 'id' ).replace( /_list$/, '_input' ) ).val( ids.join( ',' ) );
	}

	/**
	 * Accoda una miniatura alla galleria (se non c'è già) e aggiorna il campo.
	 *
	 * @param {jQuery} $list Elenco <ul> delle miniature.
	 * @param {number} id    ID dell'allegato.
	 * @param {string} thumb URL della miniatura.
	 */
	function addThumb( $list, id, thumb ) {
		if ( ! $list.children( '[data-id="' + id + '"]' ).length ) {
			$( '<li>' ).attr( 'data-id', id ).data( 'id', id ).append(
				$( '<img>', { 'class': 'onthewall-wall-thumb', src: thumb, alt: '' } ),
				$( '<button>', { type: 'button', 'class': 'button-link onthewall-gallery-remove', 'aria-label': strings.galleryRemove || '' } ).html( '&times;' )
			).appendTo( $list );
		}

		syncGallery( $list );
	}

	/**
	 * Apre la Media Library in selezione multipla e accoda le foto scelte.
	 *
	 * @param {jQuery} $button Pulsante "Aggiungi foto" cliccato.
	 */
	function openGalleryFrame( $button ) {
		var $list = $( '#' + $button.data( 'target' ) + '_gallery_list' );

		var frame = wp.media( {
			title: strings.galleryTitle || '',
			button: { text: strings.galleryButton || '' },
			library: { type: 'image' },
			multiple: 'add'
		} );

		frame.on( 'select', function () {
			frame.state().get( 'selection' ).each( function ( model ) {
				var attachment = model.toJSON();

				addThumb( $list, attachment.id, thumbUrl( attachment ) );
			} );
		} );

		frame.open();
	}

	/**
	 * Carica nella Media Library le immagini rilasciate su un riquadro, una alla
	 * volta (così l'ordine resta quello del rilascio), con l'avanzamento nel testo
	 * del riquadro. Il riquadro dell'immagine principale prende solo la prima.
	 *
	 * @param {jQuery}   $zone      Riquadro su cui sono state rilasciate.
	 * @param {FileList} files      File rilasciati.
	 * @param {Function} onUploaded Riceve ogni allegato creato.
	 */
	function uploadDropped( $zone, files, onUploaded ) {
		var $hint  = $zone.find( '.onthewall-drop-hint' ).first();
		var idle   = $hint.text();
		var errors = [];
		var images = Array.prototype.filter.call( files, function ( file ) {
			return 0 === file.type.indexOf( 'image/' );
		} );

		if ( $zone.hasClass( 'onthewall-media-drop' ) ) {
			images = images.slice( 0, 1 );
		}

		function next() {
			var file = images.shift();
			var body;

			if ( ! file ) {
				$zone.removeClass( 'is-uploading' );
				$hint.text( errors.length ? ( strings.uploadError || '' ) + ' ' + errors.join( ', ' ) : idle );
				return;
			}

			$hint.text( ( strings.uploading || '' ) + ' ' + file.name );

			body = new window.FormData();
			body.append( 'file', file, file.name );

			wp.apiFetch( { path: '/wp/v2/media', method: 'POST', body: body } )
				.then( onUploaded )
				.catch( function () {
					errors.push( file.name );
				} )
				.then( next );
		}

		if ( images.length ) {
			$zone.addClass( 'is-uploading' );
			next();
		}
	}

	$( function () {
		$( '.onthewall-gallery-list' ).sortable( {
			items: 'li',
			tolerance: 'pointer',
			update: function () {
				syncGallery( $( this ) );
			}
		} );

		// Un file rilasciato fuori dai riquadri farebbe aprire l'immagine al browser, perdendo il modulo.
		$( document ).on( 'dragover drop', function ( event ) {
			if ( ! $( event.target ).closest( '.onthewall-drop' ).length ) {
				event.preventDefault();
			}
		} );

		$( document ).on( 'dragover', '.onthewall-drop', function ( event ) {
			event.preventDefault();
			$( this ).addClass( 'is-dragover' );
		} );

		$( document ).on( 'dragleave drop', '.onthewall-drop', function () {
			$( this ).removeClass( 'is-dragover' );
		} );

		$( document ).on( 'drop', '.onthewall-drop', function ( event ) {
			var $zone  = $( this );
			var target = $zone.data( 'target' );
			var files  = event.originalEvent.dataTransfer && event.originalEvent.dataTransfer.files;

			event.preventDefault();

			if ( ! files || ! files.length ) {
				return;
			}

			uploadDropped( $zone, files, $zone.hasClass( 'onthewall-media-drop' )
				? function ( media ) {
					setMain( target, media.id, thumbUrl( media ) );
				}
				: function ( media ) {
					addThumb( $zone.find( '.onthewall-gallery-list' ), media.id, thumbUrl( media ) );
				} );
		} );

		$( document ).on( 'click', '.onthewall-gallery-add', function ( event ) {
			event.preventDefault();
			openGalleryFrame( $( this ) );
		} );

		$( document ).on( 'click', '.onthewall-gallery-remove', function ( event ) {
			var $list = $( this ).closest( '.onthewall-gallery-list' );

			event.preventDefault();
			$( this ).closest( 'li' ).remove();
			syncGallery( $list );
		} );

		$( document ).on( 'click', '.onthewall-media-button', function ( event ) {
			event.preventDefault();
			openFrame( $( this ) );
		} );

		$( document ).on( 'click', '.onthewall-media-remove', function ( event ) {
			event.preventDefault();
			clearControl( $( this ) );
		} );

		// "Venduta" blocca il prezzo: readonly (non disabled) così il valore viene comunque inviato e resta salvato.
		$( document ).on( 'change', '.onthewall-sold-toggle', function () {
			$( '#' + $( this ).data( 'price' ) ).prop( 'readOnly', this.checked );
		} );
	} );
}( jQuery ) );
