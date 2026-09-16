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

<div class="wrap">
	<?php $post_id = preg_replace( '/^(.+_)(\d+)$/', '$2', $post_type ); ?>
	<h2>
		<?php esc_html_e( 'Chart', 'unomoon-form' ); ?>
		:
		<?php echo esc_html( get_the_title( $post_id ) ); ?>
	</h2>
	<form method="post" action="">
		<?php
		wp_nonce_field( Unomoon_Form_Config::NAME . '-chart-action', Unomoon_Form_Config::NAME . '-chart-nonce-field' );
		?>
		<div id="<?php echo esc_attr( Unomoon_Form_Config::NAME . '_chart' ); ?>" class="postbox">
			<div class="inside">
				<b class="add-btn"><?php esc_html_e( 'Add Chart', 'unomoon-form' ); ?></b>
				<div class="repeatable-boxes">
					<?php foreach ( $postdata as $key => $value ) : ?>
					<div class="repeatable-box"
						<?php
						if ( 0 === $key ) :
							?>
						style="display:none"<?php endif; ?>>
						<div class="sortable-icon-handle"></div>
						<div class="remove-btn"><b>×</b></div>
						<div class="open-btn"><span><?php echo esc_html( $value['target'] ); ?></span><b>▼</b></div>
						<div class="repeatable-box-content">
							<?php esc_html_e( 'Item that create chart', 'unomoon-form' ); ?>
							<select class="targetKey" name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][target]', Unomoon_Form_Config::NAME, $post_type, $key ) ); ?>">
								<option value=""><?php esc_html_e( 'Select this.', 'unomoon-form' ); ?></option>
								<?php foreach ( $custom_keys as $custom_key_name => $custom_key_value ) : ?>
								<option value="<?php echo esc_attr( $custom_key_name ); ?>" <?php selected( $value['target'], $custom_key_name ); ?>><?php echo esc_html( $custom_key_name ); ?></option>
								<?php endforeach; ?>
							</select>
							<br />
							<?php esc_html_e( 'Chart type', 'unomoon-form' ); ?>
							<select name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][chart]', Unomoon_Form_Config::NAME, $post_type, $key ) ); ?>">
								<?php
								$chart_options = array(
									'pie' => esc_html__( 'Pie chart', 'unomoon-form' ),
									'bar' => esc_html__( 'Bar chart', 'unomoon-form' ),
								);
								foreach ( $chart_options as $chart_option_key => $chart_option ) {
									printf(
										'<option value="%s" %s>%s</option>',
										esc_attr( $chart_option_key ),
										selected( $value['chart'], $chart_option_key, false ),
										esc_html( $chart_option )
									);
								}
								?>
							</select>
							<br />
							<?php esc_html_e( 'Separator string (If the check box. If the separator attribute is not set to ",")', 'unomoon-form' ); ?>
							<input type="text" name="<?php echo esc_attr( sprintf( '%s-chart-%s[chart][%s][separator]', Unomoon_Form_Config::NAME, $post_type, $key ) ); ?>" value="<?php echo esc_attr( $value['separator'] ); ?>" size="5" />
						<!-- end .repeatable-box-content --></div>
					<!-- end .repeatable-box --></div>
					<?php endforeach; ?>
				<!-- end .repeatable-boxes --></div>
				<input type="hidden" name="<?php echo esc_attr( sprintf( '%s-formkey', Unomoon_Form_Config::NAME ) ); ?>" value="<?php echo esc_attr( $post_type ); ?>" />
				<?php submit_button(); ?>
			<!-- end .inside --></div>
		<!-- end #unomoon-form_chart --></div>
	</form>

	<?php foreach ( $chart_data as $chart_key => $chart ) : ?>
	<h3>
		<?php echo esc_html( $chart['target'] ); ?>
		<span style="font-weight:normal;font-size:14px">( <?php esc_html_e( 'The number of inquiries', 'unomoon-form' ); ?>: <?php echo (int) $chart['total']; ?> )</span>
	</h3>
	<div class="<?php echo esc_attr( Unomoon_Form_Config::NAME . '-chart-div-' . $chart_key ); ?>" data-chart-key="<?php echo esc_attr( $chart_key ); ?>" style="width: 100%; max-width: 800px"></div>
	<?php endforeach; ?>
<!-- end .wrap --></div>
