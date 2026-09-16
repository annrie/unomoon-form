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

<?php
if ( 'true' === $vertically ) {
	$vertically_class = 'vertical-item';
} else {
	$vertically_class = 'horizontal-item';
}

foreach ( $value as $_key => $_value ) {
	$value[ $_key ] = (string) $_value;
}
?>
<?php foreach ( $fields as $field_value => $field ) : ?>
	<span class="unomoonform-checkbox-field <?php echo esc_attr( $vertically_class ); ?>">
		<label <?php Unomoon_Form_Functions::input_attribute( 'for', $field['id'] ); ?>>
			<input type="checkbox"
				name="<?php echo esc_attr( $field['name'] ); ?>"
				value="<?php echo esc_attr( $field_value ); ?>"
				<?php checked( in_array( (string) $field_value, $value, true ), true, true ); ?>
				<?php Unomoon_Form_Functions::input_attribute( 'id', $field['id'] ); ?>
				<?php Unomoon_Form_Functions::input_attribute( 'class', $field['class'] ); ?>
			/>
			<span class="unomoonform-checkbox-field-text"><?php echo esc_attr( $field['label'] ); ?></span>
		</label>
	</span>
<?php endforeach; ?>
