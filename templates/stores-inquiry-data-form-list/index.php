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
	<h2><?php esc_html_e( 'Inquiry data', 'unomoon-form' ); ?></h2>
	<p>
		<?php esc_html_e( 'You can see the inquiry data that are saved in the database by clicking on the link below.', 'unomoon-form' ); ?>
	</p>
	<table class="wp-list-table widefat fixed" cellspacing="0">
		<thead>
			<th class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-title"><?php esc_html_e( 'Form title', 'unomoon-form' ); ?></th>
			<th class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-chart"><?php esc_html_e( 'Display Chart', 'unomoon-form' ); ?></th>
			<th class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-count"><?php esc_html_e( 'The number of inquiries', 'unomoon-form' ); ?></th>
			<th class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-date"><?php esc_html_e( 'Updated date', 'unomoon-form' ); ?></th>
			<th class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-date"><?php esc_html_e( 'Created date', 'unomoon-form' ); ?></th>
		</thead>
		<tbody>
			<?php
			$i = 0;
			foreach ( $form_list as $post_type => $values ) :
				$i ++;
				?>
			<tr
				<?php
				if ( 1 === $i % 2 ) {
					echo 'class="alternate"';}
				?>
			>
				<td class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-title"><?php echo esc_html( $values['title'] ); ?></td>
				<td class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-chart"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . Unomoon_Form_Config::NAME . '&page=' . Unomoon_Form_Config::NAME . '-chart&formkey=' . $post_type ) ); ?>"><?php esc_html_e( 'Display Chart', 'unomoon-form' ); ?></td>
				<td class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-count"><a href="<?php echo esc_url( admin_url( 'edit.php?post_type=' . $post_type ) ); ?>"><?php echo esc_html( $values['count'] ); ?> <?php esc_html_e( 'cases', 'unomoon-form' ); ?></a></td>
				<td class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-date"><?php echo esc_html( $values['modified_datetime'] ); ?></td>
				<td class="<?php echo esc_attr( Unomoon_Form_Config::NAME ); ?>-table-date"><?php echo esc_html( $values['created_datetime'] ); ?></td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<!-- end .wrap --></div>
