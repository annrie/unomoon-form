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
 * Unomoon_Form_Functions
 */
class Unomoon_Form_Functions {

	/**
	 * Return true when the variable passed as an argument exists and the numeric value.
	 *
	 * @param variable $value Pass by reference.
	 * @return bool
	 */
	public static function is_numeric( &$value ) {
		return ( isset( $value ) && preg_match( '/^\d+$/', $value ) );
	}

	/**
	 * Delete empty element of array.
	 *
	 * @param array $array Array.
	 * @return array
	 */
	public static function array_clean( $array ) {
		return array_filter( $array );
	}

	/**
	 * If the value is empty (0 is permitted).
	 *
	 * @param mixed $value Value.
	 * @return boolean
	 */
	public static function is_empty( $value ) {
		return ( array() === $value || '' === $value || is_null( $value ) || false === $value );
	}

	/**
	 * Unify line feed code to \n.
	 *
	 * @param string|null $string String.
	 * @return string
	 */
	public static function convert_eol( $string ) {
		return is_string( $string ) ? preg_replace( "/\r\n|\r|\n/", "\n", $string ) : '';
	}

	/**
	 * Display deprecated error message.
	 *
	 * @param string $function_name  Function name.
	 * @param string $new_function   New function name.
	 */
	public static function deprecated_message( $function_name, $new_function = '' ) {
		if ( ! defined( 'WP_DEBUG' ) || true !== WP_DEBUG || ! current_user_can( 'manage_options' ) ) {
			return;
		}

		global $unomoonform_deprecated_message;
		$unomoonform_deprecated_message .= '<div class="error ' . esc_attr( Unomoon_Form_Config::NAME ) . '-deprecated-message">';
		$unomoonform_deprecated_message .= sprintf(
			'Unomoon Form dosen\'t support "<b>%s</b>" already. This will be removed in the next version. ',
			esc_html( $function_name )
		);
		if ( $new_function ) {
			$unomoonform_deprecated_message .= sprintf( 'You should use "<b>%s</b>". ', esc_html( $new_function ) );
		}

		// phpcs:disable PHPCompatibility.FunctionUse.ArgumentFunctionsReportCurrentValue.NeedsInspection
		$debug_backtrace = debug_backtrace(); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_debug_backtrace -- Only runs when WP_DEBUG is on, to point at the deprecated call site.
		// phpcs:enable

		array_shift( $debug_backtrace );
		foreach ( $debug_backtrace as $value ) {
			if ( isset( $value['file'], $value['line'] ) ) {
				$unomoonform_deprecated_message .= sprintf( '<b>%s line %d</b>', esc_html( $value['file'] ), (int) $value['line'] );
			}
			break;
		}
		$unomoonform_deprecated_message .= '</div>';
		if ( is_admin() ) {
			if ( 'admin_notices' === current_filter() ) {
				self::_display_deprecated_message();
			} else {
				add_action( 'admin_notices', 'Unomoon_Form_Functions::_display_deprecated_message' );
			}
		} else {
			if ( 'the_content' === current_filter() ) {
				self::_display_deprecated_message();
			} else {
				add_filter( 'the_content', 'Unomoon_Form_Functions::_return_deprecated_message' );
				error_log( wp_strip_all_tags( self::_return_deprecated_message() ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Only runs when WP_DEBUG is on.
			}
		}
	}

	/**
	 * Display deprecated message.
	 */
	public static function _display_deprecated_message() {
		global $unomoonform_deprecated_message;
		$content                        = $unomoonform_deprecated_message;
		$unomoonform_deprecated_message = '';
		echo wp_kses_post( $content );
	}

	/**
	 * Return deprecated message.
	 *
	 * @param string $content Content.
	 */
	public static function _return_deprecated_message( $content = '' ) {
		global $unomoonform_deprecated_message;
		$message                        = $unomoonform_deprecated_message;
		$unomoonform_deprecated_message = '';
		return wp_kses_post( $message ) . $content;
	}

	/**
	 * Move files from Temp directory to upload directory.
	 *
	 * @param string $filepath   Path of temp file.
	 * @param string $upload_dir Directory path of new file.
	 * @param string $filename   New fine name.
	 * @return string
	 */
	public static function move_temp_file_to_upload_dir( $filepath, $upload_dir = '', $filename = '' ) {
		$wp_upload_dir = wp_upload_dir();

		if ( ! $upload_dir ) {
			$upload_dir = $wp_upload_dir['path'];
		} else {
			$upload_dir = trailingslashit( $wp_upload_dir['basedir'] ) . ltrim( $upload_dir, '/\\' );
			wp_mkdir_p( $upload_dir );
		}

		if ( ! $filename ) {
			$filename = basename( $filepath );
		}

		if ( ! preg_match( '/(\..+?)$/', $filename ) ) {
			$extension = pathinfo( $filepath, PATHINFO_EXTENSION );
			$filename  = $filename . '.' . $extension;
		}
		$filename = sanitize_file_name( $filename );
		$filename = wp_unique_filename( $upload_dir, $filename );

		$new_filepath = trailingslashit( $upload_dir ) . $filename;

		if ( $filepath === $new_filepath ) {
			return $filepath;
		}

		// If the temp file doesn't exist, return only the path after the rename
		if ( ! file_exists( $filepath ) ) {
			return $new_filepath;
		}

		// If it can move, even if it can not move, return only the path after rename
		Unomoon_Form_Directory::_filesystem()->move( $filepath, $new_filepath, true );
		return $new_filepath;
	}

	/**
	 * Save attached file on media, save attachment key (array) in posting data.
	 *
	 * @param int   $saved_mail_id Saved mail ID.
	 * @param array $attachments   Attachments.
	 * @param int   $form_id       Form ID.
	 * @return void
	 */
	public static function save_attachments_in_media( $saved_mail_id, $attachments, $form_id ) {
		// wp_generate_attachment_metadata() (image.php) and the audio/video readers it calls
		// (wp_read_audio_metadata() / wp_read_video_metadata() in media.php) live in wp-admin
		// and are not loaded on the front end.
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		if ( ! function_exists( 'wp_read_audio_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}
		$save_attached_key = array();
		foreach ( $attachments as $key => $filepath ) {
			if ( ! self::check_file_type( $filepath ) ) {
				continue;
			}

			$wp_check_filetype = wp_check_filetype( $filepath );
			$post_type         = get_post_type_object( self::get_contact_data_post_type_from_form_id( $form_id ) );
			if ( empty( $post_type->label ) ) {
				continue;
			}
			$attachment = array(
				'post_mime_type' => $wp_check_filetype['type'],
				'post_title'     => $key,
				'post_status'    => 'inherit',
				'post_content'   => __( 'Uploaded from ', 'unomoon-form' ) . $post_type->label,
			);
			$attach_id  = wp_insert_attachment( $attachment, $filepath, $saved_mail_id );
			if ( $attach_id ) {
				wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $filepath ) );
				// 代わりにここで attachment_id を保存
				update_post_meta( $saved_mail_id, $key, $attach_id );
				// $key が 添付ファイルのキーであるとわかるように隠し設定を保存
				$save_attached_key[] = $key;
			}
		}
		if ( $save_attached_key ) {
			update_post_meta( $saved_mail_id, '_' . Unomoon_Form_Config::UPLOAD_FILE_KEYS, $save_attached_key );
		}
	}

	/**
	 * Return true when correct file type.
	 *
	 * @param string $filepath Uploaded file path.
	 * @param string $filename File name.
	 * @return bool
	 */
	public static function check_file_type( $filepath, $filename = '' ) {
		if ( ! file_exists( $filepath ) ) {
			return false;
		}

		// File type restricted by WordPress (get_allowed_mime_types)
		if ( $filename ) {
			$wp_check_filetype = wp_check_filetype( $filename );
		} else {
			$wp_check_filetype = wp_check_filetype( $filepath );
		}
		if ( empty( $wp_check_filetype['type'] ) ) {
			return false;
		}

		if ( version_compare( phpversion(), '5.3.0' ) >= 0 && defined( 'FILEINFO_MIME_TYPE' ) ) {
			$finfo = new finfo( FILEINFO_MIME_TYPE );
			if ( false === $finfo ) {
				return false;
			}

			// For files have multi mime types
			switch ( $wp_check_filetype['ext'] ) {
				case 'avi':
					$wp_check_filetype['type'] = array(
						'application/x-troff-msvideo',
						'video/avi',
						'video/msvideo',
						'video/x-msvideo',
					);
					break;
				case 'mp3':
					$wp_check_filetype['type'] = array(
						'audio/mpeg3',
						'audio/x-mpeg3',
						'video/mpeg',
						'video/x-mpeg',
						'audio/mpeg',
					);
					break;
				case 'wav':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'audio/wave',
						'audio/x-wav',
						'audio/x-pn-wav',
					);
					break;
				case 'mpg':
					$wp_check_filetype['type'] = array(
						'audio/mpeg',
						'video/mpeg',
					);
					break;
				case 'docx':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'application/zip',
						'application/msword',
					);
					break;
				case 'xlsx':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'application/zip',
						'application/excel',
						'application/msexcel',
						'application/vnd.ms-excel',
					);
					break;
				case 'pptx':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'application/zip',
						'application/mspowerpoint',
						'application/powerpoint',
						'application/ppt',
					);
					break;
				case 'pdf':
					$wp_check_filetype['type'] = array(
						$wp_check_filetype['type'],
						'application/x-empty',
					);
					break;
			}

			$type = $finfo->file( $filepath );
			if ( is_array( $wp_check_filetype['type'] ) ) {
				if ( ! in_array( $type, $wp_check_filetype['type'], true ) ) {
					return false;
				}
			} else {
				if ( $type !== $wp_check_filetype['type'] ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Return display name of the tracking number.
	 *
	 * @param string $post_type Post type of inquiry data.
	 * @return string
	 */
	public static function get_tracking_number_title( $post_type ) {
		$tracking_number_title = esc_html__( 'Tracking Number', 'unomoon-form' );
		$form_key              = self::contact_data_post_type_to_form_key( $post_type );
		if ( $form_key ) {
			$tracking_number_title = apply_filters(
				'unomoonform_tracking_number_title_' . $form_key,
				$tracking_number_title
			);
		}
		return $tracking_number_title;
	}

	/**
	 * Return form key from inquiry data post type.
	 *
	 * @param string $post_type Post type of inquiry data.
	 * @return string|null
	 */
	public static function contact_data_post_type_to_form_key( $post_type ) {
		if ( self::is_contact_data_post_type( $post_type ) ) {
			if ( preg_match( '/(\d+)$/', $post_type, $match ) ) {
				$form_key = self::get_form_key_from_form_id( $match[1] );
				return $form_key;
			}
		}
	}

	/**
	 * Return form key from form ID.
	 *
	 * @param int $form_id Form ID.
	 * @return string
	 */
	public static function get_form_key_from_form_id( $form_id ) {
		if ( Unomoon_Form_Functions::is_numeric( $form_id ) ) {
			return Unomoon_Form_Config::NAME . '-' . $form_id;
		}
	}

	/**
	 * Return form ID from form key.
	 *
	 * @param string $form_key Form key.
	 * @return int
	 */
	public static function get_form_id_from_form_key( $form_key ) {
		if ( preg_match( '/^' . Unomoon_Form_Config::NAME . '-(\d+)$/', $form_key, $reg ) ) {
			return $reg[1];
		}
	}

	/**
	 * Return inquiry data post type from form ID.
	 *
	 * @param int $form_id Form ID.
	 * @return string
	 */
	public static function get_contact_data_post_type_from_form_id( $form_id ) {
		if ( Unomoon_Form_Functions::is_numeric( $form_id ) ) {
			$contact_data_post_type = Unomoon_Form_Config::DBDATA . $form_id;
			return $contact_data_post_type;
		}
	}

	/**
	 * Whether the inquiry data post type.
	 *
	 * @param string $post_type Post type name.
	 * @return boolean
	 */
	public static function is_contact_data_post_type( $post_type ) {
		return (bool) ( preg_match( '/^' . Unomoon_Form_Config::DBDATA . '\d+$/', $post_type ) );
	}

	/**
	 * Return converting attached data to appropriate HTML.
	 *
	 * @param string $value Post ID or not.
	 * @return string
	 */
	public static function get_multimedia_data( $value ) {
		$mimetype = get_post_mime_type( $value );
		if ( $mimetype ) {
			if ( in_array( $mimetype, array( 'image/jpeg', 'image/gif', 'image/png', 'image/bmp' ), true ) ) {
				// Image
				$src_thumbnail = wp_get_attachment_image_src( $value, 'thumbnail' );
				$src_full      = wp_get_attachment_image_src( $value, 'full' );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" style="max-height:50px" /></a>',
					esc_url( $src_full[0] ),
					esc_url( $src_thumbnail[0] )
				);
			} else {
				// Other
				$src = wp_mime_type_icon( $mimetype );
				return sprintf(
					'<a href="%s" target="_blank"><img src="%s" alt="" style="height:32px" /></a>',
					esc_url( wp_get_attachment_url( $value ) ),
					esc_url( $src )
				);
			}
		} else {
			// Attached, but $value is not file ID because changed meta data by hook
			return esc_html( $value );
		}
	}

	/**
	 * Return attachment file ID.
	 * 過去バージョンでの不具合でアップロードファイルを示すメタデータが空になっていることがあるのでその場合の代替処理.
	 *
	 * @param WP_Post $post     WP_Post object.
	 * @param int     $meta_key Meta data name.
	 * @return int
	 */
	public static function get_multimedia_id__fallback( $post, $meta_key ) {
		$contact_data_setting = new Unomoon_Form_Contact_Data_Setting( $post->ID );
		$index                = $contact_data_setting->get_index_of_key_in_upload_file_keys( $meta_key );

		if ( false === $index ) {
			return;
		}

		$attachments = get_posts(
			array(
				'post_type'      => 'attachment',
				'post_parent'    => $post->ID,
				'posts_per_page' => 1,
				'offset'         => $index,
			)
		);

		if ( isset( $attachments[0] ) ) {
			return $attachments[0]->ID;
		}
	}

	/**
	 * Sanitize values posted from a form.
	 *
	 * Form values are free text and are escaped on every output, so the
	 * sanitization here is limited to what must never reach storage:
	 * invalid UTF-8 sequences and NUL bytes. Arrays are handled recursively.
	 *
	 * @param mixed $value Posted value (already unslashed).
	 * @return mixed
	 */
	public static function sanitize_posted_value( $value ) {
		if ( is_array( $value ) ) {
			return array_map( array( __CLASS__, 'sanitize_posted_value' ), $value );
		}

		if ( is_scalar( $value ) ) {
			return wp_kses_no_null( wp_check_invalid_utf8( (string) $value ) );
		}

		return '';
	}

	/**
	 * Enqueue the bundled jQuery UI theme stylesheet (smoothness).
	 *
	 * Bundled so that no asset is loaded from a third-party CDN.
	 *
	 * @return void
	 */
	public static function enqueue_jquery_ui_style() {
		wp_enqueue_style(
			Unomoon_Form_Config::NAME . '-jquery-ui',
			UNOMOON_FORM_PLUGIN_URL . '/css/vendor/jquery-ui/jquery-ui.min.css',
			array(),
			'1.14.1'
		);
	}

	/**
	 * Enqueue Unomoon Form assets.
	 *
	 * @param int $form_id Form ID.
	 * @return void
	 */
	public static function unomoonform_enqueue_scripts( $form_id ) {
		$Setting  = new Unomoon_Form_Setting( $form_id );
		$form_key = Unomoon_Form_Functions::get_form_key_from_form_id( $form_id );
		$url      = UNOMOON_FORM_PLUGIN_URL;
		wp_enqueue_style( Unomoon_Form_Config::NAME, $url . '/css/style.css', array(), UNOMOON_FORM_VERSION );

		$style  = $Setting->get( 'style' );
		$styles = apply_filters( 'unomoonform_styles', array() );
		if ( is_array( $styles ) && isset( $styles[ $style ] ) ) {
			$css = $styles[ $style ];
			wp_enqueue_style( Unomoon_Form_Config::NAME . '_style_' . $form_key, $css, array(), UNOMOON_FORM_VERSION );
		}

		wp_enqueue_script( Unomoon_Form_Config::NAME, $url . '/js/form.js', array( 'jquery' ), UNOMOON_FORM_VERSION, true );
		do_action( 'unomoonform_enqueue_scripts_' . $form_key );
	}

	/**
	 * Generate input field's attribute and attribute value pair.
	 *
	 * @param string $attribute_name  Attribute name.
	 * @param string $attribute_value Attribute value.
	 * @return string
	 */
	public static function generate_input_attribute( $attribute_name, $attribute_value ) {
		if ( is_null( $attribute_value ) ) {
			return;
		}

		return sprintf(
			'%1$s="%2$s"',
			esc_html( $attribute_name ),
			esc_attr( $attribute_value )
		);
	}

	/**
	 * Output an input field's attribute and attribute value pair, escaped.
	 *
	 * Echoing variant of generate_input_attribute() for use in templates.
	 *
	 * @param string $attribute_name  Attribute name.
	 * @param string $attribute_value Attribute value.
	 * @return void
	 */
	public static function input_attribute( $attribute_name, $attribute_value ) {
		if ( is_null( $attribute_value ) ) {
			return;
		}

		printf(
			'%1$s="%2$s"',
			esc_html( $attribute_name ),
			esc_attr( $attribute_value )
		);
	}
}
