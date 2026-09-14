<?php
/**
 * Schermata "Gestisci The Wall": modifica di tutte le opere in un'unica pagina.
 *
 * Vive dentro wp-admin, sotto il menu "Opere". Non è raggiungibile dal front-end
 * ed è protetta dal login di WordPress (capability edit_posts). Permette di
 * aggiornare titolo, immagine, misura, tecnica e ordine di tutte le opere con un
 * solo salvataggio, e di aggiungerne una nuova.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Memorizza (e restituisce) l'hook suffix della pagina di gestione.
 *
 * Serve a limitare l'enqueue degli asset alla sola schermata giusta.
 *
 * @param string|null $set Valore da memorizzare, oppure null per leggerlo.
 * @return string
 */
function onthewall_artwork_manager_hook( $set = null ) {
	static $hook = '';

	if ( null !== $set ) {
		$hook = (string) $set;
	}

	return $hook;
}

/**
 * Aggiunge la sottovoce "Gestisci The Wall" sotto il menu Opere.
 */
function onthewall_add_artwork_manager_page() {
	$hook = add_submenu_page(
		'edit.php?post_type=artwork',
		esc_html__( 'Gestisci The Wall', 'onthewall' ),
		esc_html__( 'Gestisci The Wall', 'onthewall' ),
		'edit_posts',
		'onthewall-manage-wall',
		'onthewall_render_artwork_manager_page'
	);

	if ( $hook ) {
		onthewall_artwork_manager_hook( $hook );
		add_action( 'load-' . $hook, 'onthewall_handle_artwork_manager_save' );
	}
}
add_action( 'admin_menu', 'onthewall_add_artwork_manager_page' );

/**
 * Accoda media library, script e stile solo nella schermata di gestione.
 *
 * Nota: questo JavaScript vive esclusivamente in wp-admin e non tocca il
 * front-end, che resta senza JS come da preview approvata.
 *
 * @param string $suffix Hook suffix della schermata corrente.
 */
function onthewall_artwork_manager_assets( $suffix ) {
	if ( $suffix !== onthewall_artwork_manager_hook() ) {
		return;
	}

	$theme_uri = get_template_directory_uri();

	wp_enqueue_media();

	wp_enqueue_style(
		'onthewall-admin',
		$theme_uri . '/assets/css/admin.css',
		array(),
		onthewall_asset_version( '/assets/css/admin.css' )
	);

	wp_enqueue_script(
		'onthewall-admin-artwork-manager',
		$theme_uri . '/assets/js/admin-artwork-manager.js',
		array( 'jquery' ),
		onthewall_asset_version( '/assets/js/admin-artwork-manager.js' ),
		true
	);

	wp_localize_script(
		'onthewall-admin-artwork-manager',
		'onthewallArtworkManager',
		array(
			'frameTitle'  => esc_html__( 'Scegli l\'immagine dell\'opera', 'onthewall' ),
			'frameButton' => esc_html__( 'Usa questa immagine', 'onthewall' ),
		)
	);
}
add_action( 'admin_enqueue_scripts', 'onthewall_artwork_manager_assets' );

/**
 * Elabora il salvataggio del form (pattern Post/Redirect/Get).
 *
 * Aggancia il salvataggio a load-{page} così, dopo aver scritto, può
 * reindirizzare ed evitare il reinvio del form al refresh.
 */
function onthewall_handle_artwork_manager_save() {
	$method = isset( $_SERVER['REQUEST_METHOD'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) : '';

	if ( 'POST' !== $method ) {
		return;
	}

	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Non hai i permessi per gestire le opere.', 'onthewall' ) );
	}

	check_admin_referer( 'onthewall_manage_wall', 'onthewall_manage_wall_nonce' );

	$updated = 0;
	$created = 0;
	$deleted = 0;

	// --- Opere esistenti ---------------------------------------------------
	$rows = ( isset( $_POST['artwork'] ) && is_array( $_POST['artwork'] ) )
		? wp_unslash( $_POST['artwork'] )
		: array();

	foreach ( $rows as $raw_id => $data ) {
		$id = absint( $raw_id );

		if ( ! $id || 'artwork' !== get_post_type( $id ) || ! current_user_can( 'edit_post', $id ) ) {
			continue;
		}

		if ( ! empty( $data['delete'] ) ) {
			wp_trash_post( $id );
			$deleted++;
			continue;
		}

		wp_update_post(
			array(
				'ID'         => $id,
				'post_title' => isset( $data['title'] ) ? sanitize_text_field( $data['title'] ) : '',
				'menu_order' => isset( $data['order'] ) ? (int) $data['order'] : 0,
			)
		);

		update_post_meta( $id, '_onthewall_size', isset( $data['size'] ) ? sanitize_text_field( $data['size'] ) : '' );
		update_post_meta( $id, '_onthewall_technique', isset( $data['technique'] ) ? sanitize_text_field( $data['technique'] ) : '' );

		$thumb = isset( $data['thumbnail_id'] ) ? absint( $data['thumbnail_id'] ) : 0;

		if ( $thumb ) {
			set_post_thumbnail( $id, $thumb );
		} else {
			delete_post_thumbnail( $id );
		}

		$updated++;
	}

	// --- Nuova opera -------------------------------------------------------
	$new = ( isset( $_POST['artwork_new'] ) && is_array( $_POST['artwork_new'] ) )
		? wp_unslash( $_POST['artwork_new'] )
		: array();

	$new_title = isset( $new['title'] ) ? sanitize_text_field( $new['title'] ) : '';
	$new_thumb = isset( $new['thumbnail_id'] ) ? absint( $new['thumbnail_id'] ) : 0;

	if ( '' !== $new_title || $new_thumb ) {
		$new_id = wp_insert_post(
			array(
				'post_type'   => 'artwork',
				'post_status' => 'publish',
				'post_title'  => '' !== $new_title ? $new_title : esc_html__( 'Nuova opera', 'onthewall' ),
				'menu_order'  => isset( $new['order'] ) ? (int) $new['order'] : 0,
			)
		);

		if ( $new_id && ! is_wp_error( $new_id ) ) {
			update_post_meta( $new_id, '_onthewall_size', isset( $new['size'] ) ? sanitize_text_field( $new['size'] ) : '' );
			update_post_meta( $new_id, '_onthewall_technique', isset( $new['technique'] ) ? sanitize_text_field( $new['technique'] ) : '' );

			if ( $new_thumb ) {
				set_post_thumbnail( $new_id, $new_thumb );
			}

			$created++;
		}
	}

	$redirect = add_query_arg(
		array(
			'post_type'         => 'artwork',
			'page'              => 'onthewall-manage-wall',
			'onthewall_updated' => $updated,
			'onthewall_created' => $created,
			'onthewall_deleted' => $deleted,
		),
		admin_url( 'edit.php' )
	);

	wp_safe_redirect( $redirect );
	exit;
}

/**
 * Mostra l'avviso di esito dopo un salvataggio.
 */
function onthewall_artwork_manager_notice() {
	if ( ! isset( $_GET['onthewall_updated'], $_GET['onthewall_created'], $_GET['onthewall_deleted'] ) ) {
		return;
	}

	$updated = absint( $_GET['onthewall_updated'] );
	$created = absint( $_GET['onthewall_created'] );
	$deleted = absint( $_GET['onthewall_deleted'] );

	$parts = array();

	if ( $updated ) {
		/* translators: %d: numero di opere aggiornate. */
		$parts[] = sprintf( _n( '%d opera aggiornata', '%d opere aggiornate', $updated, 'onthewall' ), $updated );
	}

	if ( $created ) {
		/* translators: %d: numero di opere create. */
		$parts[] = sprintf( _n( '%d opera creata', '%d opere create', $created, 'onthewall' ), $created );
	}

	if ( $deleted ) {
		/* translators: %d: numero di opere spostate nel cestino. */
		$parts[] = sprintf( _n( '%d opera spostata nel cestino', '%d opere spostate nel cestino', $deleted, 'onthewall' ), $deleted );
	}

	if ( empty( $parts ) ) {
		$parts[] = esc_html__( 'Nessuna modifica.', 'onthewall' );
	}

	printf(
		'<div class="notice notice-success is-dismissible"><p>%s</p></div>',
		esc_html( implode( ' · ', $parts ) )
	);
}

/**
 * Stampa i controlli per scegliere/rimuovere l'immagine in evidenza.
 *
 * @param string $base     Prefisso per gli id degli elementi (univoco per riga).
 * @param string $name     Prefisso per l'attributo name (es. "artwork[12]").
 * @param int    $thumb_id ID dell'allegato attualmente impostato.
 */
function onthewall_render_media_control( $base, $name, $thumb_id ) {
	$url            = $thumb_id ? wp_get_attachment_image_url( $thumb_id, 'thumbnail' ) : '';
	$preview_class  = 'onthewall-wall-thumb' . ( $url ? '' : ' hidden' );
	$remove_class   = 'button-link onthewall-media-remove' . ( $url ? '' : ' hidden' );
	?>
	<img id="<?php echo esc_attr( $base ); ?>_preview" class="<?php echo esc_attr( $preview_class ); ?>" src="<?php echo esc_url( $url ); ?>" alt="" />
	<input type="hidden" id="<?php echo esc_attr( $base ); ?>_input" name="<?php echo esc_attr( $name ); ?>[thumbnail_id]" value="<?php echo esc_attr( (string) $thumb_id ); ?>" />
	<p class="onthewall-media-actions">
		<button type="button" class="button onthewall-media-button" data-target="<?php echo esc_attr( $base ); ?>">
			<?php esc_html_e( 'Scegli immagine', 'onthewall' ); ?>
		</button>
		<button type="button" class="<?php echo esc_attr( $remove_class ); ?>" data-target="<?php echo esc_attr( $base ); ?>">
			<?php esc_html_e( 'Rimuovi', 'onthewall' ); ?>
		</button>
	</p>
	<?php
}

/**
 * Stampa una riga della tabella per un'opera esistente.
 *
 * @param WP_Post $artwork Opera da modificare.
 */
function onthewall_render_artwork_manager_row( $artwork ) {
	$id        = (int) $artwork->ID;
	$size      = (string) get_post_meta( $id, '_onthewall_size', true );
	$technique = (string) get_post_meta( $id, '_onthewall_technique', true );
	$thumb_id  = (int) get_post_thumbnail_id( $id );
	$base      = 'artwork_' . $id;
	$name      = 'artwork[' . $id . ']';
	?>
	<tr>
		<td class="onthewall-col-image">
			<?php onthewall_render_media_control( $base, $name, $thumb_id ); ?>
		</td>
		<td>
			<label class="screen-reader-text" for="<?php echo esc_attr( $base ); ?>_title"><?php esc_html_e( 'Titolo', 'onthewall' ); ?></label>
			<input type="text" id="<?php echo esc_attr( $base ); ?>_title" class="widefat" name="<?php echo esc_attr( $name ); ?>[title]" value="<?php echo esc_attr( $artwork->post_title ); ?>" />
		</td>
		<td>
			<input type="text" class="widefat" name="<?php echo esc_attr( $name ); ?>[size]" value="<?php echo esc_attr( $size ); ?>" placeholder="80×80" />
		</td>
		<td>
			<input type="text" class="widefat" name="<?php echo esc_attr( $name ); ?>[technique]" value="<?php echo esc_attr( $technique ); ?>" placeholder="spray · stencil · canvas" />
		</td>
		<td>
			<input type="number" class="small-text" name="<?php echo esc_attr( $name ); ?>[order]" value="<?php echo esc_attr( (string) (int) $artwork->menu_order ); ?>" />
		</td>
		<td>
			<label>
				<input type="checkbox" name="<?php echo esc_attr( $name ); ?>[delete]" value="1" />
				<?php esc_html_e( 'Nel cestino', 'onthewall' ); ?>
			</label>
		</td>
	</tr>
	<?php
}

/**
 * Renderizza l'intera schermata "Gestisci The Wall".
 */
function onthewall_render_artwork_manager_page() {
	if ( ! current_user_can( 'edit_posts' ) ) {
		wp_die( esc_html__( 'Non hai i permessi per gestire le opere.', 'onthewall' ) );
	}

	$artworks = get_posts(
		array(
			'post_type'   => 'artwork',
			'post_status' => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'numberposts' => -1,
			'orderby'     => 'menu_order',
			'order'       => 'ASC',
		)
	);
	?>
	<div class="wrap onthewall-wall-manager">
		<h1><?php esc_html_e( 'Gestisci The Wall', 'onthewall' ); ?></h1>
		<p class="description">
			<?php esc_html_e( 'Modifica tutte le opere della griglia in un\'unica schermata. Le modifiche vengono salvate tutte insieme con il pulsante in fondo.', 'onthewall' ); ?>
		</p>

		<?php onthewall_artwork_manager_notice(); ?>

		<form method="post" action="">
			<?php wp_nonce_field( 'onthewall_manage_wall', 'onthewall_manage_wall_nonce' ); ?>

			<table class="wp-list-table widefat fixed striped onthewall-wall-table">
				<thead>
					<tr>
						<th scope="col" class="onthewall-col-image"><?php esc_html_e( 'Immagine', 'onthewall' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Titolo', 'onthewall' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Misura', 'onthewall' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Tecnica', 'onthewall' ); ?></th>
						<th scope="col" class="onthewall-col-order"><?php esc_html_e( 'Ordine', 'onthewall' ); ?></th>
						<th scope="col" class="onthewall-col-delete"><?php esc_html_e( 'Elimina', 'onthewall' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php
					if ( empty( $artworks ) ) {
						printf(
							'<tr><td colspan="6">%s</td></tr>',
							esc_html__( 'Nessuna opera presente. La home mostra le opere di default finché non ne aggiungi una qui sotto.', 'onthewall' )
						);
					}

					foreach ( $artworks as $artwork ) {
						onthewall_render_artwork_manager_row( $artwork );
					}
					?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Aggiungi una nuova opera', 'onthewall' ); ?></h2>
			<table class="form-table onthewall-new-artwork" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Immagine', 'onthewall' ); ?></th>
						<td><?php onthewall_render_media_control( 'artwork_new', 'artwork_new', 0 ); ?></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="artwork_new_title"><?php esc_html_e( 'Titolo', 'onthewall' ); ?></label>
						</th>
						<td><input type="text" id="artwork_new_title" class="regular-text" name="artwork_new[title]" value="" /></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="artwork_new_size"><?php esc_html_e( 'Misura', 'onthewall' ); ?></label>
						</th>
						<td><input type="text" id="artwork_new_size" class="regular-text" name="artwork_new[size]" value="" placeholder="80×80" /></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="artwork_new_technique"><?php esc_html_e( 'Tecnica', 'onthewall' ); ?></label>
						</th>
						<td><input type="text" id="artwork_new_technique" class="regular-text" name="artwork_new[technique]" value="" placeholder="spray · stencil · canvas" /></td>
					</tr>
					<tr>
						<th scope="row">
							<label for="artwork_new_order"><?php esc_html_e( 'Ordine', 'onthewall' ); ?></label>
						</th>
						<td><input type="number" id="artwork_new_order" class="small-text" name="artwork_new[order]" value="0" /></td>
					</tr>
				</tbody>
			</table>

			<?php submit_button( __( 'Salva tutte le opere', 'onthewall' ) ); ?>
		</form>
	</div>
	<?php
}
