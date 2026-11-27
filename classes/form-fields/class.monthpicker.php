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
 * Unomoon_Form_Field_Monthpicker
 */
class Unomoon_Form_Field_Monthpicker extends Unomoon_Form_Abstract_Form_Field {

	/**
	 * Types of form type.
	 * input|select|button|input_button|error|other.
	 *
	 * @var string
	 */
	public $type = 'input';

	/**
	 * Set shortcode_name and display_name.
	 * Overwrite required for each child class.
	 *
	 * @return array
	 */
	protected function set_names() {
		return array(
			'shortcode_name' => 'unomoonform_monthpicker',
			'display_name'   => __( 'Monthpicker', 'unomoon-form' ),
		);
	}

	/**
	 * Set default attributes.
	 *
	 * @return array
	 */
	protected function set_defaults() {
		return array(
			'name'        => '',
			'id'          => null,
			'class'       => null,
			'size'        => 30,
			'js'          => '',
			'value'       => '',
			'placeholder' => null,
			'show_error'  => 'true',
		);
	}

	/**
	 * Callback of add shortcode for input page.
	 *
	 * @return string
	 */
	protected function input_page() {
		Unomoon_Form_Functions::enqueue_jquery_ui_style();

		wp_enqueue_style(
			'jquery-ui-monthpicker',
			UNOMOON_FORM_PLUGIN_URL . '/js/jquery-ui-month-picker/MonthPicker.min.css',
			array(),
			UNOMOON_FORM_VERSION
		);

		wp_enqueue_script(
			'jquery-ui-monthpicker',
			UNOMOON_FORM_PLUGIN_URL . '/js/jquery-ui-month-picker/MonthPicker.js',
			array( 'jquery', 'jquery-ui-button', 'jquery-ui-datepicker' ),
			UNOMOON_FORM_VERSION,
			true
		);

		$Json_Parser      = new Unomoon_Form_Json_Parser( $this->atts['js'] );
		$this->atts['js'] = $Json_Parser->create_json();
		$js               = json_decode( $this->atts['js'], true );
		$js               = is_array( $js ) ? $this->_strip_tags_deep( $js ) : array();

		$js = array_merge(
			$js,
			array(
				'Button' => 'false',
			)
		);

		$translate_monthpicker = apply_filters( 'unomoonform_translate_monthpicker_' . $this->form_key, true );
		if ( $translate_monthpicker && 'ja' === get_locale() ) {
			$js = array_merge(
				array(
					'i18n'        => array(
						'year'   => '',
						'months' => array( '1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月' ),
					),
					'MonthFormat' => 'yy年mm月',
				),
				$js
			);
		}

		$this->atts['js'] = wp_json_encode( $js );

		// Initialise the widget through the script API instead of an inline <script> tag in the template.
		wp_add_inline_script(
			'jquery-ui-monthpicker',
			sprintf(
				'jQuery( function( $ ) { $( %s ).MonthPicker( %s ); } );',
				wp_json_encode( 'input[name="' . $this->atts['name'] . '"]' ),
				wp_json_encode( $js )
			)
		);

		$value = $this->Data->get_raw( $this->atts['name'] );
		if ( is_null( $value ) ) {
			$value = $this->atts['value'];
		}

		$_ret  = '';
		$_ret .= $this->Form->monthpicker(
			$this->atts['name'],
			array(
				'id'          => $this->atts['id'],
				'class'       => $this->atts['class'],
				'size'        => $this->atts['size'],
				'js'          => $this->atts['js'],
				'value'       => $value,
				'placeholder' => $this->atts['placeholder'],
			)
		);
		if ( 'false' !== $this->atts['show_error'] ) {
			$_ret .= $this->get_error( $this->atts['name'] );
		}
		return $_ret;
	}

	/**
	 * Callback of add shortcode for confirm page.
	 *
	 * @return string
	 */
	protected function confirm_page() {
		$value = $this->Data->get_raw( $this->atts['name'] );
		$_ret  = esc_html( $value );
		$_ret .= $this->Form->hidden( $this->atts['name'], $value );
		return $_ret;
	}

	/**
	 * Display tag generator dialog.
	 * Overwrite required for each child class.
	 *
	 * @param array $options Options.
	 */
	public function unomoonform_tag_generator_dialog( array $options = array() ) {
		?>
		<p>
			<strong>name<span class="unomoonform_require">*</span></strong>
			<?php $name = $this->get_value_for_generator( 'name', $options ); ?>
			<input type="text" name="name" value="<?php echo esc_attr( $name ); ?>" />
		</p>
		<p>
			<strong>id</strong>
			<?php $id = $this->get_value_for_generator( 'id', $options ); ?>
			<input type="text" name="id" value="<?php echo esc_attr( $id ); ?>" />
		</p>
		<p>
			<strong>class</strong>
			<?php $class = $this->get_value_for_generator( 'class', $options ); ?>
			<input type="text" name="class" value="<?php echo esc_attr( $class ); ?>" />
		</p>
		<p>
			<strong>size</strong>
			<?php $size = $this->get_value_for_generator( 'size', $options ); ?>
			<input type="text" name="size" value="<?php echo esc_attr( $size ); ?>" />
		</p>
		<p>
			<strong>JavaScript</strong>
			<?php $js = $this->get_value_for_generator( 'js', $options ); ?>
			<input type="text" name="js" value="<?php echo esc_attr( $js ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Default value', 'unomoon-form' ); ?></strong>
			<?php $value = $this->get_value_for_generator( 'value', $options ); ?>
			<input type="text" name="value" value="<?php echo esc_attr( $value ); ?>" />
		</p>
		<p>
			<strong>placeholder</strong>
			<?php $placeholder = $this->get_value_for_generator( 'placeholder', $options ); ?>
			<input type="text" name="placeholder" value="<?php echo esc_attr( $placeholder ); ?>" />
		</p>
		<p>
			<strong><?php esc_html_e( 'Display error', 'unomoon-form' ); ?></strong>
			<?php $show_error = $this->get_value_for_generator( 'show_error', $options ); ?>
			<label><input type="checkbox" name="show_error" value="false" <?php checked( 'false', $show_error ); ?> /> <?php esc_html_e( 'Don\'t display error.', 'unomoon-form' ); ?></label>
		</p>
		<?php
	}

	/**
	 * Strip HTML tags from every string in the MonthPicker options.
	 *
	 * The options come from the shortcode's js attribute, which anyone who can edit
	 * forms can set, including users without unfiltered_html. MonthPicker and jQuery UI
	 * render some of them (button labels, month names) as HTML, so no option may carry markup.
	 *
	 * @param array $options MonthPicker options.
	 * @return array
	 */
	protected function _strip_tags_deep( array $options ) {
		foreach ( $options as $key => $value ) {
			if ( is_array( $value ) ) {
				$options[ $key ] = $this->_strip_tags_deep( $value );
			} elseif ( is_string( $value ) ) {
				$options[ $key ] = wp_strip_all_tags( $value );
			}
		}
		return $options;
	}
}
