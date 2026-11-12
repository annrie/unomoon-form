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
 * Unomoon_Form_Parser
 */
class Unomoon_Form_Parser {

	/**
	 * @var string
	 */
	protected $form_key;

	/**
	 * @var Unomoon_Form_Setting
	 */
	protected $Setting;

	/**
	 * @var Unomoon_Form_Data
	 */
	protected $Data;

	/**
	 * @var string
	 */
	protected static $pattern = '/{(.+?)}/';

	/**
	 * Constructor.
	 *
	 * @param Unomoon_Form_Setting $Setting Unomoon_Form_Setting object.
	 */
	public function __construct( $Setting ) {
		$this->Setting  = $Setting;
		$form_id        = $Setting->get( 'post_id' );
		$this->form_key = Unomoon_Form_Functions::get_form_key_from_form_id( $form_id );
		$this->Data     = Unomoon_Form_Data::connect( $this->form_key );
	}

	/**
	 * Replace {name} for mail destination.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function replace_for_mail_destination( $value ) {
		return $this->_replace( $value, array( $this, '_replace_for_mail_destination_callback' ) );
	}

	/**
	 * Callback for replace_for_mail_destination.
	 *
	 * @param array $matches $matches of preg_replace_callback.
	 * @return string|null
	 */
	protected function _replace_for_mail_destination_callback( $matches ) {
		$match = $matches[1];
		$value = Unomoon_Form_Parser::apply_filters_unomoonform_custom_mail_tag( $this->form_key, null, $match );

		// Return blank when custom mail tag isn't use(= null)
		if ( is_null( $value ) ) {
			return '';
		}
		return $value;
	}

	/**
	 * Replace {name} for mail content.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function replace_for_mail_content( $value ) {
		return $this->_replace( $value, array( $this, '_replace_for_mail_content_callback' ) );
	}

	/**
	 * Callback for replace_for_mail_content.
	 *
	 * @param array $matches $matches of preg_replace_callback.
	 * @return string|null
	 */
	protected function _replace_for_mail_content_callback( $matches ) {
		$match = $matches[1];
		return $this->parse( $match );
	}

	/**
	 * Replace {name} for complete page.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function replace_for_complete_page( $value ) {
		return $this->_replace( $value, array( $this, '_replace_for_complete_page_callback' ) );
	}

	/**
	 * Callback for replace_for_complete_page.
	 * Neutralizes shortcode brackets so that substituted values are never parsed as shortcodes.
	 *
	 * @param array $matches $matches of preg_replace_callback.
	 * @return string|null
	 */
	protected function _replace_for_complete_page_callback( $matches ) {
		$match = $matches[1];
		$value = $this->parse( $match );

		if ( is_null( $value ) ) {
			return $value;
		}

		return str_replace( array( '[', ']' ), array( '&#91;', '&#93;' ), $value );
	}

	/**
	 * Replace {name} for input and confirm page.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	public function replace_for_page( $value ) {
		$value = $this->_replace_user_property( $value );
		$value = $this->_replace_post_property( $value );
		return $value;
	}

	/**
	 * Replace {foo} in the form. e.g. $post->foo.
	 *
	 * @param string $value Value.
	 * @return string
	 */
	protected function _replace_post_property( $value ) {
		if ( $this->Setting->get( 'querystring' ) ) {
			$callback = array( $this, '_get_post_property_from_querystring' );
		} else {
			$callback = array( $this, '_get_post_property_from_this' );
		}

		return $this->_replace( $value, $callback );
	}

	/**
	 * Callback from preg_replace_callback when enabled querystring setting.
	 *
	 * @param array $matches $matches of preg_replace_callback.
	 * @return string|null
	 */
	protected function _get_post_property_from_querystring( $matches ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only lookup of a published post.
		$post_id = isset( $_GET['post_id'] ) ? absint( wp_unslash( $_GET['post_id'] ) ) : 0;
		if ( ! $post_id ) {
			return;
		}

		$post = get_post( $post_id );
		if ( empty( $post->ID ) ) {
			return;
		}

		// Checks both the post type and the status: inquiry data is stored as "publish" under a non-public post type.
		// Inquiry data is also rejected by name, because other code can filter is_post_publicly_viewable().
		if (
			Unomoon_Form_Functions::is_contact_data_post_type( $post->post_type )
			|| ! is_post_publicly_viewable( $post )
			|| post_password_required( $post )
		) {
			return;
		}

		/**
		 * Filters the meta keys that may be substituted from the post given by the post_id query string.
		 * None by default: any visitor can choose the post, so only the keys allowed here are read.
		 * Post properties such as post_title are not affected.
		 *
		 * @param string[] $meta_keys Allowed meta keys.
		 * @param WP_Post  $post      Post looked up by post_id.
		 * @param string   $form_key  Form key.
		 */
		$allowed_meta_keys = apply_filters( 'unomoonform_querystring_allowed_meta_keys', array(), $post, $this->form_key );
		if ( $this->form_key ) {
			$allowed_meta_keys = apply_filters( 'unomoonform_querystring_allowed_meta_keys_' . $this->form_key, $allowed_meta_keys, $post );
		}

		// Only string values count; a map such as array( 'key' => true ) or nested arrays would otherwise become wrong keys.
		$allowed_meta_keys = array_values( array_filter( (array) $allowed_meta_keys, 'is_string' ) );

		return $this->_get_post_property( $post, $matches[1], $allowed_meta_keys );
	}

	/**
	 * Callback from preg_replace_callback when disabled querystring setting.
	 *
	 * @param array $matches $matches of preg_replace_callback.
	 * @return string|null
	 */
	protected function _get_post_property_from_this( $matches ) {
		global $post;

		if ( ! is_singular() ) {
			return;
		}

		if ( empty( $post->ID ) ) {
			return;
		}

		return $this->_get_post_property( $post, $matches[1] );
	}

	/**
	 * Get WP_Post property.
	 *
	 * @param WP_Post|null  $post              WP_Post object.
	 * @param string        $meta_key          Meta data name.
	 * @param string[]|null $allowed_meta_keys Meta keys that may be read. Null allows any non-private meta.
	 * @return string|null
	 */
	protected function _get_post_property( $post, $meta_key, $allowed_meta_keys = null ) {
		if ( ! is_a( $post, 'WP_Post' ) ) {
			return;
		}

		// Only public, display-safe post fields can be substituted; everything else (post_password, ...) is off limits.
		$allowed_properties = array(
			'ID',
			'post_author',
			'post_date',
			'post_date_gmt',
			'post_title',
			'post_excerpt',
			'post_name',
			'post_modified',
			'post_modified_gmt',
			'post_parent',
			'guid',
			'menu_order',
			'post_type',
			'post_mime_type',
			'comment_count',
		);
		if ( in_array( $meta_key, $allowed_properties, true ) ) {
			return esc_html( (string) $post->$meta_key );
		}

		// Private meta (leading underscore) is never exposed.
		if ( '' === $meta_key || '_' === substr( $meta_key, 0, 1 ) ) {
			if ( is_array( $allowed_meta_keys ) && in_array( $meta_key, $allowed_meta_keys, true ) ) {
				$this->_notice_skipped_meta_key( $meta_key, 'Meta keys that start with an underscore are never substituted, even when allowed.' );
			}
			return;
		}

		if ( is_array( $allowed_meta_keys ) && ! in_array( $meta_key, $allowed_meta_keys, true ) ) {
			// Tell developers only when the post really has this meta, so ordinary placeholders stay quiet.
			if ( metadata_exists( 'post', $post->ID, $meta_key ) ) {
				$this->_notice_skipped_meta_key( $meta_key, 'Allow it with the unomoonform_querystring_allowed_meta_keys filter.' );
			}
			return;
		}

		$post_meta = get_post_meta( $post->ID, $meta_key, true );
		if ( ! is_scalar( $post_meta ) ) {
			return;
		}

		return esc_html( (string) $post_meta );
	}

	/**
	 * Report, once per meta key, that a placeholder was left empty under the querystring setting.
	 * _doing_it_wrong() only shows anything when WP_DEBUG is enabled.
	 *
	 * @param string $meta_key Meta key that was not substituted.
	 * @param string $reason   What to do about it.
	 */
	protected function _notice_skipped_meta_key( $meta_key, $reason ) {
		static $noticed = array();
		if ( isset( $noticed[ $meta_key ] ) ) {
			return;
		}
		$noticed[ $meta_key ] = true;

		_doing_it_wrong(
			__METHOD__,
			esc_html(
				sprintf(
					'The placeholder {%1$s} was left empty because the querystring setting does not substitute post meta unless it is allowed. %2$s',
					$meta_key,
					$reason
				)
			),
			'5.1.8.2'
		);
	}

	/**
	 * Replace {property of user} when logged in.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	protected function _replace_user_property( $content ) {
		$user   = wp_get_current_user();
		$search = array(
			'{user_id}',
			'{user_login}',
			'{user_email}',
			'{user_url}',
			'{user_registered}',
			'{display_name}',
		);

		if ( ! empty( $user ) ) {
			$content = str_replace(
				$search,
				array(
					esc_html( (string) $user->get( 'ID' ) ),
					esc_html( (string) $user->get( 'user_login' ) ),
					esc_html( (string) $user->get( 'user_email' ) ),
					esc_html( (string) $user->get( 'user_url' ) ),
					esc_html( (string) $user->get( 'user_registered' ) ),
					esc_html( (string) $user->get( 'display_name' ) ),
				),
				$content
			);
		} else {
			$content = str_replace( $search, '', $content );
		}

		return $content;
	}

	/**
	 * Search {$name}.
	 *
	 * @param string $value   Value.
	 * @return array
	 */
	public static function search( $value ) {
		preg_match_all(
			self::$pattern,
			$value,
			$matches
		);

		return $matches;
	}

	/**
	 * そのキーについて送信された値を返す.
	 *
	 * @param string $name Posted field name.
	 * @return string
	 */
	public function parse( $name ) {
		$form_id = $this->Setting->get( 'post_id' );

		// Unomoon_Form_Config::TRACKINGNUMBER のときはお問い合せ番号を参照する
		if ( Unomoon_Form_Config::TRACKINGNUMBER === $name ) {
			if ( $form_id ) {
				return $this->Setting->get_tracking_number( $form_id );
			}
		}

		// @see https://github.com/inc2734/unomoon-form/issues/99
		if ( Unomoon_Form_Config::TRACKINGNUMBER . '_for_complete_page' === $name ) {
			if ( $form_id ) {
				return $this->Setting->get_tracking_number( $form_id ) - 1;
			}
		}

		$value = $this->Data->get( $name );
		$value = Unomoon_Form_Parser::apply_filters_unomoonform_custom_mail_tag(
			$this->Data->get_form_key(),
			$value,
			$name,
			$this->Data->get_saved_mail_id()
		);
		return $value;
	}

	/**
	 * Apply unomoonform_custom_mail_tag filter hook.
	 *
	 * @param string      $form_key      Form key.
	 * @param string|null $value         Value.
	 * @param string      $name          Custom mail tag name.
	 * @param string      $saved_mail_id Saved mail ID.
	 * @return string
	 */
	public static function apply_filters_unomoonform_custom_mail_tag( $form_key, $value, $name, $saved_mail_id = null ) {
		$value = apply_filters(
			'unomoonform_custom_mail_tag',
			$value,
			$name,
			$saved_mail_id
		);

		$value = apply_filters(
			'unomoonform_custom_mail_tag_' . $form_key,
			$value,
			$name,
			$saved_mail_id
		);
		return $value;
	}

	/**
	 * Replace {name} for mail content.
	 *
	 * @param string   $value    Value.
	 * @param function $callback Callback of preg_replace_callback.
	 * @return string
	 */
	protected static function _replace( $value, $callback ) {
		return preg_replace_callback(
			self::$pattern,
			$callback,
			$value
		);
	}
}
