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

<input type="hidden" name="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>_nonce" value="<?php echo esc_attr( wp_create_nonce( Unomoon_Form_Config::NAME ) ); ?>" />
<table border="0" cellpadding="0" cellspacing="0">
	<?php
	$columns  = array();
	$values   = $contact_data_setting->gets();
	$_columns = array();
	foreach ( $values as $key => $value ) {
		if ( Unomoon_Form_Config::TRACKINGNUMBER === $key ) {
			$columns[ $key ] = Unomoon_Form_Functions::get_tracking_number_title( $post_type );
			continue;
		}
		if ( in_array( $key, $contact_data_setting->get_permit_keys(), true ) ) {
			continue;
		}
		$_columns[ $key ] = $key;
	}
	$_columns = apply_filters( 'unomoonform_inquiry_data_columns-' . $post_type, $_columns );
	$columns  = array_merge( $columns, $_columns );
	?>

	<?php foreach ( $columns as $key => $label ) : ?>
		<?php if ( isset( $values[ $key ] ) ) : ?>
	<tr>
		<th>
			<?php
			if ( Unomoon_Form_Config::TRACKINGNUMBER === $key ) {
				echo esc_html( Unomoon_Form_Functions::get_tracking_number_title( $post_type ) );
			} else {
				echo esc_html( $label );
			}
			?>
		</th>
		<td>
			<?php
			if ( $contact_data_setting->is_upload_file_key( $key ) ) {
				// 過去バージョンでの不具合でメタデータが空になっていることがあるのでその場合は代替処理
				if ( '' === $values[ $key ] ) {
					$values[ $key ] = Unomoon_Form_Functions::get_multimedia_id__fallback( $post, $key );
				}
				echo wp_kses_post( Unomoon_Form_Functions::get_multimedia_data( $values[ $key ] ) );
			} else {
				echo nl2br( esc_html( $values[ $key ] ) );
			}
			?>
		</td>
	</tr>
	<?php endif; ?>
	<?php endforeach; ?>
	<tr>
		<th><?php esc_html_e( 'Admin Email To', 'unomoon-form' ); ?></th>
		<td>
			<?php echo esc_html( $contact_data_setting->get( 'admin_mail_to' ) ); ?>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Response Status', 'unomoon-form' ); ?></th>
		<td>
			<select name="<?php echo esc_attr( Unomoon_Form_Config::INQUIRY_DATA_NAME ); ?>[response_status]">
				<?php foreach ( $contact_data_setting->get_response_statuses() as $key => $value ) : ?>
				<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $contact_data_setting->get( 'response_status' ) ); ?>>
					<?php echo esc_html( $value ); ?>
				</option>
				<?php endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<th><?php esc_html_e( 'Memo', 'unomoon-form' ); ?></th>
		<td><textarea name="<?php echo esc_attr( Unomoon_Form_Config::INQUIRY_DATA_NAME ); ?>[memo]" cols="50" rows="5"><?php echo esc_textarea( $contact_data_setting->get( 'memo' ) ); ?></textarea></td>
	</tr>
</table>
