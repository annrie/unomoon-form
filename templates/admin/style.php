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
?>

<p>
	<select name="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>[style]">
		<option value=""><?php esc_html_e( 'Select Style', 'unomoon-form' ); ?></option>
		<?php foreach ( $styles as $style_key => $css ) : ?>
		<option value="<?php echo esc_attr( $style_key ); ?>" <?php selected( $style, $style_key ); ?>>
			<?php echo esc_html( $style_key ); ?>
		</option>
		<?php endforeach; ?>
	</select>
</p>
