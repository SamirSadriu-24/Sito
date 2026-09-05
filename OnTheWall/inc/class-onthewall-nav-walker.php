<?php
/**
 * Walker della navigazione principale.
 *
 * Riproduce la nav della preview: link inline, senza <ul>/<li>, con colore
 * di hover ciclico primary → secondary → chart-4.
 *
 * @package OnTheWall
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class OnTheWall_Nav_Walker
 */
class OnTheWall_Nav_Walker extends Walker_Nav_Menu {

	/**
	 * Contatore delle voci di primo livello.
	 *
	 * @var int
	 */
	protected $item_index = 0;

	/**
	 * Nessun wrapper per i sottolivelli: la nav della preview è a un solo livello.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Profondità.
	 * @param stdClass $args   Argomenti.
	 */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Nessun wrapper di chiusura.
	 *
	 * @param string   $output Output.
	 * @param int      $depth  Profondità.
	 * @param stdClass $args   Argomenti.
	 */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * Output della singola voce come <a>.
	 *
	 * @param string   $output            Output.
	 * @param WP_Post  $data_object       Voce di menu.
	 * @param int      $depth             Profondità.
	 * @param stdClass $args              Argomenti.
	 * @param int      $current_object_id ID corrente.
	 */
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		if ( $depth > 0 ) {
			return;
		}

		$hover_colors = array( 'hover:text-primary', 'hover:text-secondary', 'hover:text-chart-4' );
		$hover        = $hover_colors[ $this->item_index % count( $hover_colors ) ];
		++$this->item_index;

		$classes = 'transition-colors ' . $hover;
		$title   = apply_filters( 'the_title', $data_object->title, $data_object->ID );

		$attributes  = ! empty( $data_object->url ) ? ' href="' . esc_url( $data_object->url ) . '"' : '';
		$attributes .= ! empty( $data_object->target ) ? ' target="' . esc_attr( $data_object->target ) . '"' : '';
		$attributes .= ! empty( $data_object->xfn ) ? ' rel="' . esc_attr( $data_object->xfn ) . '"' : '';

		$output .= '<a' . $attributes . ' class="' . esc_attr( $classes ) . '">' . esc_html( $title ) . '</a>';
	}

	/**
	 * Nessuna chiusura aggiuntiva.
	 *
	 * @param string   $output      Output.
	 * @param WP_Post  $data_object Voce di menu.
	 * @param int      $depth       Profondità.
	 * @param stdClass $args        Argomenti.
	 */
	public function end_el( &$output, $data_object, $depth = 0, $args = null ) {}
}

/**
 * Fallback della nav principale: le tre voci della preview approvata.
 */
function onthewall_primary_menu_fallback() {
	$items = array(
		array(
			'label' => esc_html__( 'About', 'onthewall' ),
			'url'   => '#about',
			'hover' => 'hover:text-primary',
		),
		array(
			'label' => esc_html__( 'Works', 'onthewall' ),
			'url'   => '#works',
			'hover' => 'hover:text-secondary',
		),
		array(
			'label' => esc_html__( 'Commissions', 'onthewall' ),
			'url'   => '#commissions',
			'hover' => 'hover:text-chart-4',
		),
	);

	foreach ( $items as $item ) {
		printf(
			'<a href="%1$s" class="transition-colors %2$s">%3$s</a>',
			esc_url( $item['url'] ),
			esc_attr( $item['hover'] ),
			esc_html( $item['label'] )
		);
	}
}
