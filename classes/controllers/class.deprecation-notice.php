<?php
/**
 * @package unomoon-form
 * @author websoudan
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unomoon_Form_Deprecation_Notice_Controller
 *
 * Displays a deprecation notice across every admin page when forms that use
 * shortcodes scheduled for removal exist. Split out from the form edit
 * controller so that the notice appears even when administrators are not
 * currently editing a form.
 */
class Unomoon_Form_Deprecation_Notice_Controller {

	/**
	 * Cache key for the list of forms using shortcodes scheduled for removal.
	 */
	const CACHE_KEY = 'unomoonform_deprecated_shortcodes_forms';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'admin_notices', array( $this, '_notice' ) );
		add_action( 'save_post_' . Unomoon_Form_Config::NAME, array( $this, '_invalidate_cache' ) );
		add_action( 'deleted_post', array( $this, '_invalidate_cache' ) );
		add_action( 'trashed_post', array( $this, '_invalidate_cache' ) );
		add_action( 'untrashed_post', array( $this, '_invalidate_cache' ) );
	}

	/**
	 * Display an admin notice listing all forms that use shortcodes
	 * scheduled for removal in a future release.
	 */
	public function _notice() {
		if ( ! current_user_can( Unomoon_Form_Config::CAPABILITY ) ) {
			return;
		}

		$affected_forms = $this->_get_forms_using_deprecated_shortcodes();
		if ( empty( $affected_forms ) ) {
			return;
		}

		$list_items = array();
		foreach ( $affected_forms as $form ) {
			$edit_link = get_edit_post_link( $form->ID );
			$title     = '' !== (string) $form->post_title
				? $form->post_title
				: __( '(no title)', 'unomoon-form' );

			if ( $edit_link ) {
				$list_items[] = sprintf(
					'<a href="%1$s">%2$s</a>',
					esc_url( $edit_link ),
					esc_html( $title )
				);
			} else {
				$list_items[] = esc_html( $title );
			}
		}

		?>
		<div class="notice notice-warning">
			<p>
				<strong><?php esc_html_e( 'Unomoon Form: Notice of feature removal', 'unomoon-form' ); ?></strong><br>
				<?php
				printf(
					/* translators: 1: Version number, 2: Planned year of removal. */
					esc_html__( 'The [unomoonform_file] and [unomoonform_image] shortcodes will be removed in version %1$s (planned for release within %2$s).', 'unomoon-form' ),
					'5.2',
					'2026'
				);
				?>
			</p>
			<p>
				<?php esc_html_e( 'The following form(s) currently use these shortcodes:', 'unomoon-form' ); ?>
				<?php echo implode( ', ', $list_items ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Individual items escaped above. ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Return unomoon-form posts whose content contains shortcodes scheduled
	 * for removal.
	 *
	 * @return array<object{ID:int,post_title:string}>
	 */
	protected function _get_forms_using_deprecated_shortcodes() {
		$cached = get_transient( self::CACHE_KEY );
		if ( false !== $cached ) {
			return $cached;
		}

		global $wpdb;

		$like_file  = '%' . $wpdb->esc_like( '[unomoonform_file' ) . '%';
		$like_image = '%' . $wpdb->esc_like( '[unomoonform_image' ) . '%';

		$candidates = $wpdb->get_results( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- LIKE on post_content has no WP_Query equivalent; the result is cached in a transient above.
			$wpdb->prepare(
				"SELECT ID, post_title, post_content FROM {$wpdb->posts}
				 WHERE post_type = %s
				   AND post_status NOT IN ( 'trash', 'auto-draft' )
				   AND ( post_content LIKE %s OR post_content LIKE %s )
				 ORDER BY post_title ASC",
				Unomoon_Form_Config::NAME,
				$like_file,
				$like_image
			)
		);

		$results = array();
		if ( is_array( $candidates ) ) {
			foreach ( $candidates as $row ) {
				// LIKE can match other shortcodes whose name begins with
				// "unomoonform_file" or "unomoonform_image" (e.g. "unomoonform_filepicker").
				// Re-check with a shortcode-aware regex.
				if ( preg_match( '/\[(unomoonform_file|unomoonform_image)(\s|\])/', (string) $row->post_content ) ) {
					$results[] = (object) array(
						'ID'         => (int) $row->ID,
						'post_title' => (string) $row->post_title,
					);
				}
			}
		}

		set_transient( self::CACHE_KEY, $results, HOUR_IN_SECONDS );
		return $results;
	}

	/**
	 * Invalidate the cached list of forms using deprecated shortcodes.
	 * Fires when a form is saved, deleted, trashed, or untrashed.
	 *
	 * @param int $post_id Post ID.
	 */
	public function _invalidate_cache( $post_id = 0 ) {
		if ( $post_id && Unomoon_Form_Config::NAME !== get_post_type( $post_id ) ) {
			return;
		}
		delete_transient( self::CACHE_KEY );
	}
}
