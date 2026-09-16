<?php
/**
 * Plugin Name: Unomoon Form
 * Plugin URI: https://cielos.phantomoon.com/unomoon-form/
 * Description: Shortcode-based contact form with a confirmation screen. A maintained fork of MW WP Form, tracking its security fixes and verified on WordPress 7.
 * Version: 5.1.6.3
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * Author: annrie
 * Author URI: https://phantomoon.com
 * Original Author: inc2734
 * Original Author URI: https://2inc.org
 * Text Domain: unomoon-form
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * @package unomoon-form
 * @author annrie
 * @license GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'UNOMOON_FORM_VERSION', '5.1.6.3' );
define( 'UNOMOON_FORM_PLUGIN_FILE', __FILE__ );
define( 'UNOMOON_FORM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'UNOMOON_FORM_PLUGIN_URL', untrailingslashit( plugin_dir_url( __FILE__ ) ) );

/**
 * Include files.
 */
include_once( UNOMOON_FORM_PLUGIN_DIR . 'classes/functions.php' );
include_once( UNOMOON_FORM_PLUGIN_DIR . 'classes/config.php' );
include_once( UNOMOON_FORM_PLUGIN_DIR . 'classes/deprecated.php' );

class Unomoon_Form {

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'plugins_loaded', array( $this, '_load_initialize_files' ), 9 );
		add_action( 'plugins_loaded', array( $this, '_initialize' ), 11 );

		register_uninstall_hook( __FILE__, array( __CLASS__, '_uninstall' ) );
	}

	/**
	 * Load classes.
	 */
	public function _load_initialize_files() {
		$plugin_dir_path = UNOMOON_FORM_PLUGIN_DIR;
		$includes        = array(
			'/classes/abstract',
			'/classes/controllers',
			'/classes/models',
			'/classes/services',
			'/classes/validation-rules',
			'/classes/form-fields',
		);
		foreach ( $includes as $include ) {
			foreach ( glob( $plugin_dir_path . $include . '/*.php' ) as $file ) {
				require_once( $file );
			}
		}
	}

	/**
	 * The starting point of the process.
	 */
	public function _initialize() {
		Unomoon_Form_Csrf::save_token();

		add_action( 'after_setup_theme', array( $this, '_after_setup_theme' ), 11 );
		add_action( 'init', array( $this, '_register_post_type' ) );
		add_action( 'template_redirect', array( $this, '_do_empty_temp_dir' ) );
	}

	/**
	 * Initialize each screens.
	 */
	public function _after_setup_theme() {
		if ( current_user_can( Unomoon_Form_Config::CAPABILITY ) && is_admin() ) {
			add_action( 'admin_enqueue_scripts', array( $this, '_admin_enqueue_scripts' ) );
			add_action( 'admin_menu', array( $this, '_admin_menu_for_chart' ) );
			add_action( 'admin_menu', array( $this, '_admin_menu_for_inquiry_data_list' ) );
			add_action( 'current_screen', array( $this, '_current_screen' ) );
			new Unomoon_Form_Deprecation_Notice_Controller();
		} elseif ( ! is_admin() ) {
			new Unomoon_Form_Main_Controller();
		}
	}

	/**
	 * Enqueue assets.
	 */
	public function _admin_enqueue_scripts() {
		$url = UNOMOON_FORM_PLUGIN_URL;
		wp_enqueue_style( Unomoon_Form_Config::NAME . '-admin-common', $url . '/css/admin-common.css', array(), UNOMOON_FORM_VERSION );
	}

	/**
	 * Add admin menu for chart.
	 */
	public function _admin_menu_for_chart() {
		$contact_data_post_types = Unomoon_Form_Contact_Data_Setting::get_form_post_types();
		if ( empty( $contact_data_post_types ) ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=' . Unomoon_Form_Config::NAME,
			esc_html__( 'Chart', 'unomoon-form' ),
			esc_html__( 'Chart', 'unomoon-form' ),
			Unomoon_Form_Config::CAPABILITY,
			Unomoon_Form_Config::NAME . '-chart',
			'__return_false'
		);
	}

	/**
	 * Add admin menu for saved inquiry data.
	 */
	public function _admin_menu_for_inquiry_data_list() {
		$contact_data_post_types = Unomoon_Form_Contact_Data_Setting::get_form_post_types();
		if ( empty( $contact_data_post_types ) ) {
			return;
		}

		add_submenu_page(
			'edit.php?post_type=' . Unomoon_Form_Config::NAME,
			__( 'Inquiry data', 'unomoon-form' ),
			__( 'Inquiry data', 'unomoon-form' ),
			Unomoon_Form_Config::CAPABILITY,
			Unomoon_Form_Config::NAME . '-save-data',
			'__return_false'
		);
	}

	/**
	 * Front controller.
	 *
	 * @param WP_Screen $screen WP_Screen object.
	 */
	public function _current_screen( $screen ) {
		if ( Unomoon_Form_Config::NAME === $screen->id ) {
			new Unomoon_Form_Admin_Controller();
		} elseif ( 'edit-' . Unomoon_Form_Config::NAME === $screen->id ) {
			new Unomoon_Form_Admin_List_Controller();
		} elseif ( Unomoon_Form_Functions::is_contact_data_post_type( $screen->id ) ) {
			new Unomoon_Form_Contact_Data_Controller();
		} elseif ( preg_match( '/^edit-' . Unomoon_Form_Config::DBDATA . '\d+$/', $screen->id ) ) {
			new Unomoon_Form_Contact_Data_List_Controller();
		} elseif ( Unomoon_Form_Config::NAME . '_page_' . Unomoon_Form_Config::NAME . '-chart' === $screen->id ) {
			new Unomoon_Form_Chart_Controller();
		} elseif ( Unomoon_Form_Config::NAME . '_page_' . Unomoon_Form_Config::NAME . '-save-data' === $screen->id ) {
			new Unomoon_Form_Stores_Inquiry_Data_Form_List_Controller();
		}
	}

	/**
	 * Register post types for Unomoon Form and inquiry data.
	 */
	public function _register_post_type() {
		if ( ! current_user_can( Unomoon_Form_Config::CAPABILITY ) && is_admin() ) {
			return;
		}

		register_post_type(
			Unomoon_Form_Config::NAME,
			array(
				'label'           => __( 'Unomoon Form', 'unomoon-form' ),
				'labels'          => array(
					'name'               => __( 'Unomoon Form', 'unomoon-form' ),
					'singular_name'      => __( 'Unomoon Form', 'unomoon-form' ),
					'add_new_item'       => __( 'Add New Form', 'unomoon-form' ),
					'edit_item'          => __( 'Edit Form', 'unomoon-form' ),
					'new_item'           => __( 'New Form', 'unomoon-form' ),
					'view_item'          => __( 'View Form', 'unomoon-form' ),
					'search_items'       => __( 'Search Forms', 'unomoon-form' ),
					'not_found'          => __( 'No Forms found', 'unomoon-form' ),
					'not_found_in_trash' => __( 'No Forms found in Trash', 'unomoon-form' ),
				),
				'capability_type' => 'page',
				'public'          => false,
				'show_ui'         => true,
			)
		);

		$admin = new Unomoon_Form_Admin();
		$forms = $admin->get_forms_using_database();
		foreach ( $forms as $form ) {
			$post_type = Unomoon_Form_Functions::get_contact_data_post_type_from_form_id( $form->ID );
			register_post_type(
				$post_type,
				array(
					'label'           => $form->post_title,
					'labels'          => array(
						'name'               => $form->post_title,
						'singular_name'      => $form->post_title,
						'edit_item'          => __( 'Edit ', 'unomoon-form' ) . ':' . $form->post_title,
						'view_item'          => __( 'View', 'unomoon-form' ) . ':' . $form->post_title,
						'search_items'       => __( 'Search', 'unomoon-form' ) . ':' . $form->post_title,
						'not_found'          => __( 'No data found', 'unomoon-form' ),
						'not_found_in_trash' => __( 'No data found in Trash', 'unomoon-form' ),
					),
					'capability_type' => 'page',
					'public'          => false,
					'show_ui'         => true,
					'show_in_menu'    => false,
					'supports'        => array( 'title' ),
				)
			);
		}
	}

	/**
	 * Uninstall processes.
	 */
	public static function _uninstall() {
		$plugin_dir_path = UNOMOON_FORM_PLUGIN_DIR;
		include_once( $plugin_dir_path . 'classes/models/class.admin.php' );
		include_once( $plugin_dir_path . 'classes/models/class.file.php' );
		include_once( $plugin_dir_path . 'classes/models/class.directory.php' );

		$admin = new Unomoon_Form_Admin();
		$forms = $admin->get_forms();

		$data_post_ids = array();
		foreach ( $forms as $form ) {
			$data_post_ids[] = $form->ID;
			wp_delete_post( $form->ID, true );
		}

		foreach ( $data_post_ids as $data_post_id ) {
			delete_option( Unomoon_Form_Config::NAME . '-chart-' . $data_post_id );

			$data_posts = get_posts(
				array(
					'post_type'      => Unomoon_Form_Functions::get_contact_data_post_type_from_form_id( $data_post_id ),
					'posts_per_page' => -1,
				)
			);
			if ( empty( $data_posts ) ) {
				continue;
			}

			foreach ( $data_posts as $data_post ) {
				wp_delete_post( $data_post->ID, true );
			}
		}

		try {
			Unomoon_Form_Directory::do_empty( Unomoon_Form_Directory::get(), true );
			Unomoon_Form_Directory::remove( Unomoon_Form_Directory::get( false ) );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Report cleanup failures on uninstall to the PHP error log.
		}

		delete_option( Unomoon_Form_Config::NAME );
	}

	public function _do_empty_temp_dir() {
		try {
			Unomoon_Form_Directory::do_empty( Unomoon_Form_Directory::get() );
		} catch ( \Exception $e ) {
			error_log( $e->getMessage() ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log -- Report temp directory cleanup failures to the PHP error log.
		}
	}
}

new Unomoon_Form();
