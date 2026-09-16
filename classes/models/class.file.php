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
 * Unomoon_Form_File
 */
class Unomoon_Form_File {

	/**
	 * Temporary directory used while wp_handle_upload() runs.
	 *
	 * @var string
	 */
	protected $upload_dir = '';

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_filter( 'upload_mimes', array( $this, '_upload_mimes' ) );
	}

	/**
	 * Add mimes.
	 *
	 * @param array $t Array of MIME types.
	 */
	public function _upload_mimes( $t ) {
		$t['psd'] = 'image/vnd.adobe.photoshop';
		$t['eps'] = 'application/octet-stream';
		$t['ai']  = 'application/pdf';
		return $t;
	}

	/**
	 * Upload all files.
	 *
	 * @param int $form_id The form ID.
	 * @param array $files Array of upload files.
	 * @return array
	 */
	public function upload( $form_id, array $files = array() ) {
		$uploaded_files = array();
		foreach ( $files as $name => $file ) {
			$uploaded_file = $this->_single_file_upload( $form_id, $name, $file );
			if ( ! $uploaded_file ) {
				continue;
			}
			$uploaded_files[ $name ] = $uploaded_file;
		}

		return $uploaded_files;
	}

	/**
	 * 指定したファイルをアップロード.
	 *
	 * @param int $form_id The form ID.
	 * @param string $name Field name.
	 * @param array A value of $_FIELS
	 * @return string
	 */
	protected function _single_file_upload( $form_id, $name, $file ) {
		if ( empty( $file['tmp_name'] ) ) {
			return false;
		}

		$error = $file['error'];

		try {
			if ( UPLOAD_ERR_OK !== $error && UPLOAD_ERR_NO_FILE !== $error ||
				! Unomoon_Form_Functions::check_file_type( $file['tmp_name'], $file['name'] ) ) {
				if ( UPLOAD_ERR_INI_SIZE === $error || UPLOAD_ERR_FORM_SIZE === $error ) {
					throw new \RuntimeException( '[Unomoon Form] File size of the uploaded file is too large.' );
				}
				throw new \RuntimeException( '[Unomoon Form] An error occurred during file upload.' );
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Report upload failures to the PHP error log.
			return false;
		}

		try {
			$new_user_file_dir = Unomoon_Form_Directory::generate_user_file_dirpath( $form_id, $name );
			if ( ! wp_mkdir_p( $new_user_file_dir ) ) {
				throw new \RuntimeException( '[Unomoon Form] Creation of a temporary directory for file upload failed.' );
			}
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Report upload failures to the PHP error log.
			return false;
		}

		Unomoon_Form_Directory::do_empty( $new_user_file_dir, true );

		$filename = sanitize_file_name( sprintf( '%1$s-%2$s', $name, $file['name'] ) );
		try {
			$filepath = Unomoon_Form_Directory::generate_user_filepath( $form_id, $name, $filename );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Report upload failures to the PHP error log.
			return false;
		}
		if ( ! $filepath ) {
			return false;
		}

		// Hand the file to WordPress' uploader (type/extension checks, safe moving) while keeping our temporary directory.
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}

		$this->upload_dir = $new_user_file_dir;
		add_filter( 'upload_dir', array( $this, '_temp_upload_dir' ) );
		$uploaded = wp_handle_upload(
			$file,
			array(
				'test_form'                => false,
				'unique_filename_callback' => function () use ( $filename ) {
					return $filename;
				},
			)
		);
		remove_filter( 'upload_dir', array( $this, '_temp_upload_dir' ) );
		$this->upload_dir = '';

		if ( ! is_array( $uploaded ) || isset( $uploaded['error'] ) ) {
			$message = is_array( $uploaded ) && isset( $uploaded['error'] ) ? $uploaded['error'] : 'unknown error';
			error_log( '[Unomoon Form] There was an error saving the uploaded file: ' . $message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Report upload failures to the PHP error log.
			return false;
		}

		if ( wp_normalize_path( $uploaded['file'] ) !== $filepath ) {
			// wp_handle_upload() saved the file elsewhere; keep the temporary directory clean.
			wp_delete_file( $uploaded['file'] );
			error_log( '[Unomoon Form] The uploaded file was saved to an unexpected location.' ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Report upload failures to the PHP error log.
			return false;
		}

		return $filename;
	}

	/**
	 * Point wp_handle_upload() at the per-session temporary directory.
	 *
	 * @param array $uploads Upload directory data from wp_upload_dir().
	 * @return array
	 */
	public function _temp_upload_dir( $uploads ) {
		if ( ! $this->upload_dir ) {
			return $uploads;
		}

		$relative        = str_replace( $uploads['basedir'], '', $this->upload_dir );
		$uploads['path']   = $this->upload_dir;
		$uploads['url']    = $uploads['baseurl'] . $relative;
		$uploads['subdir'] = $relative;
		return $uploads;
	}
}
