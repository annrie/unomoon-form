<?php
/**
 * @package unomoon-form
 * @author websoudan
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound -- Included from _render() after extract(); these variables are method-local, not global.

do_action( 'unomoonform_tag_generator_dialog' );

$types = array(
	'input'        => 'input',
	'select'       => 'select',
	'button'       => 'button',
	'input_button' => 'input_button',
	'error'        => 'error',
	'other'        => 'other',
);
$group = apply_filters( 'unomoonform_tag_generator_group', $types );

$labels = array(
	'input'        => __( 'Input fields', 'unomoon-form' ),
	'select'       => __( 'Select fields', 'unomoon-form' ),
	'button'       => __( 'Button fields (button)', 'unomoon-form' ),
	'input_button' => __( 'Button fields (input)', 'unomoon-form' ),
	'error'        => __( 'Error fields', 'unomoon-form' ),
	'other'        => __( 'Other fields', 'unomoon-form' ),
);
$labels = apply_filters( 'unomoonform_tag_generator_labels', $labels );
?>
<div class="add-unomoonform-btn">
	<select>
		<option value=""><?php echo esc_html_e( 'Select this.', 'unomoon-form' ); ?></option>
		<?php foreach ( $group as $type ) : ?>
			<?php
			$label = isset( $labels[ $type ] ) ? $labels[ $type ] : $type;
			$tag   = 'other' === $type ? 'unomoonform_tag_generator_option' : 'unomoonform_tag_generator_' . $type . '_option';
			?>
			<optgroup label="<?php echo esc_attr( $label ); ?>">
				<?php do_action( $tag ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.DynamicHooknameFound -- $tag is built above with the unomoonform_tag_generator_ prefix. ?>
			</optgroup>
		<?php endforeach; ?>
	</select>
	<span class="button"><?php esc_html_e( 'Add form tag', 'unomoon-form' ); ?></span>
</div>
