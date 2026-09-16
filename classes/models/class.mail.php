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
 * Unomoon_Form_Mail
 */
class Unomoon_Form_Mail {

	/**
	 * @var string
	 */
	public $to;

	/**
	 * @var string
	 */
	public $cc;

	/**
	 * @var string
	 */
	public $bcc;

	/**
	 * @var string
	 */
	public $reply_to;

	/**
	 * @var string
	 */
	public $from;

	/**
	 * @var string
	 */
	public $return_path;

	/**
	 * @var string
	 */
	public $sender;

	/**
	 * @var string
	 */
	public $subject;

	/**
	 * @var string
	 */
	public $body;

	/**
	 * @var array
	 */
	public $attachments = array();

	/**
	 * @var Unomoon_Form_Mail_Parser
	 */
	protected $Mail_Parser;

	/**
	 * Send mail.
	 *
	 * @return boolean
	 */
	public function send() {
		$this->to       = trim( $this->to );
		$this->cc       = trim( $this->cc );
		$this->bcc      = trim( $this->bcc );
		$this->reply_to = trim( $this->reply_to );

		if ( ! $this->to ) {
			return apply_filters( 'unomoonform_is_mail_sended', false );
		}

		add_action( 'phpmailer_init', array( $this, '_set_return_path' ) );
		add_filter( 'wp_mail_from', array( $this, '_set_mail_from' ) );
		add_filter( 'wp_mail_from_name', array( $this, '_set_mail_from_name' ) );

		$headers = array();

		if ( $this->cc ) {
			$headers[] = 'Cc: ' . $this->cc;
		}

		if ( $this->bcc ) {
			$headers[] = 'Bcc: ' . $this->bcc;
		}

		if ( $this->reply_to ) {
			$headers[] = 'Reply-To: ' . $this->reply_to;
		}

		if ( ! $this->body ) {
			// wp_mail do not send mail when message is empty.
			$this->body = ' ';
		}

		$is_mail_sended = wp_mail( $this->to, $this->subject, $this->body, $headers, array_values( $this->attachments ) );

		remove_action( 'phpmailer_init', array( $this, '_set_return_path' ) );
		remove_filter( 'wp_mail_from', array( $this, '_set_mail_from' ) );
		remove_filter( 'wp_mail_from_name', array( $this, '_set_mail_from_name' ) );

		return apply_filters( 'unomoonform_is_mail_sended', $is_mail_sended );
	}

	/**
	 * Set mail from.
	 *
	 * @param string $email E-mail.
	 * @return string
	 */
	public function _set_mail_from( $email ) {
		if ( filter_var( $this->from, FILTER_VALIDATE_EMAIL ) ) {
			return $this->from;
		}
		if ( ! filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
			$admin_email = get_option( 'admin_email' );
			if ( filter_var( $admin_email, FILTER_VALIDATE_EMAIL ) ) {
				$this->from = $admin_email;
				return $admin_email;
			}
		}
		$this->from = $email;
		return $email;
	}

	/**
	 * Set sender (from name).
	 *
	 * @param string $sender Sender.
	 * @return string
	 */
	public function _set_mail_from_name( $sender ) {
		if ( $this->sender ) {
			return $this->sender;
		}
		$this->sender = $sender;
		return $sender;
	}

	/**
	 * Set Return-Path.
	 *
	 * @param phpmailer $phpmailer phpmailer object.
	 */
	public function _set_return_path( $phpmailer ) {
		if ( $this->return_path ) {
			if ( filter_var( $this->return_path, FILTER_VALIDATE_EMAIL ) ) {
				$phpmailer->Sender = $this->return_path;
			}
		}
	}

	/**
	 * Create mail content from array.
	 *
	 * @param array $array   Posted key and value.
	 * @param array $options Options.
	 * @return string
	 */
	public function createBody( array $array, array $options = array() ) {
		$_ret     = '';
		$defaults = array(
			'exclude' => array(), // phpcs:ignore WordPressVIPMinimum.Performance.WPQueryParams.PostNotIn_exclude -- Option of createBody(), not a query argument.
		);
		$options  = array_merge( $defaults, $options );
		foreach ( $array as $key => $value ) {
			if ( in_array( $key, $options['exclude'], true ) ) {
				continue;
			}
			if ( is_array( $value ) && isset( $value['separator'], $value['data'] ) ) {
				$_value = '';
				if ( is_array( $value['data'] ) ) {
					foreach ( $value['data'] as $_val ) {
						if ( '' !== $_val && ! is_null( $_val ) ) {
							$_value = implode( $value['separator'], $value['data'] );
							break;
						}
					}
				} else {
					$_value = $value['data'];
				}
				$value = $_value;
			}
			if ( $value ) {
				$_ret .= sprintf( "▼%s\n%s\n\n", esc_html( $key ), esc_html( $value ) );
			}
		}
		return $_ret;
	}

	/**
	 * Set defaults setting for admin mail.
	 *
	 * @param Unomoon_Form_Setting $Setting Unomoon_Form_Setting object.
	 */
	public function set_admin_mail_raw_params( Unomoon_Form_Setting $Setting ) {
		$this->subject     = $Setting->get( 'admin_mail_subject' );
		$this->body        = $Setting->get( 'admin_mail_content' );
		$this->to          = $Setting->get( 'mail_to' );
		$this->cc          = $Setting->get( 'mail_cc' );
		$this->bcc         = $Setting->get( 'mail_bcc' );
		$this->reply_to    = $Setting->get( 'admin_mail_reply_to' );
		$this->from        = $Setting->get( 'admin_mail_from' );
		$this->return_path = $Setting->get( 'mail_return_path' );
		$this->sender      = $Setting->get( 'admin_mail_sender' );
	}

	/**
	 * Set defaults setting for reply mail.
	 *
	 * @param Unomoon_Form_Setting $Setting Unomoon_Form_Setting object.
	 */
	public function set_reply_mail_raw_params( Unomoon_Form_Setting $Setting ) {
		$this->to          = '';
		$this->cc          = '';
		$this->bcc         = '';
		$this->attachments = array();

		$form_id               = $Setting->get( 'post_id' );
		$form_key              = Unomoon_Form_Functions::get_form_key_from_form_id( $form_id );
		$Data                  = Unomoon_Form_Data::connect( $form_key );
		$automatic_reply_email = $Setting->get( 'automatic_reply_email' );

		if ( ! $form_id ) {
			return;
		}

		$Validation              = new Unomoon_Form_Validation_Rule_Mail( $Data );
		$is_invalid_mail_address = $Validation->rule(
			$automatic_reply_email
		);

		if ( $automatic_reply_email && ! $is_invalid_mail_address ) {
			$this->to = $Data->get_post_value_by_key( $automatic_reply_email );
		}

		$this->reply_to    = $Setting->get( 'mail_reply_to' );
		$this->return_path = $Setting->get( 'mail_return_path' );
		$this->from        = $Setting->get( 'mail_from' );
		$this->sender      = $Setting->get( 'mail_sender' );
		$this->subject     = $Setting->get( 'mail_subject' );
		$this->body        = $Setting->get( 'mail_content' );
	}

	/**
	 * Replace {name} to content in mail content.
	 *
	 * @param Unomoon_Form_Setting $Setting Unomoon_Form_Setting object.
	 */
	public function parse( $Setting ) {
		$this->Mail_Parser = new Unomoon_Form_Mail_Parser( $this, $Setting );
		$Mail              = $this->Mail_Parser->get_parsed_mail_object();
		foreach ( get_object_vars( $Mail ) as $key => $value ) {
			$this->$key = $value;
		}
	}

	/**
	 * Save to database.
	 *
	 * @param Unomoon_Form_Setting $Setting Unomoon_Form_Setting object.
	 * @return int
	 */
	public function save( $Setting ) {
		$this->Mail_Parser = new Unomoon_Form_Mail_Parser( $this, $Setting );
		$this->Mail_Parser->save();
		return $this->get_saved_mail_id();
	}

	/**
	 * Return saved mail ID.
	 *
	 * @return int
	 */
	public function get_saved_mail_id() {
		if ( $this->Mail_Parser ) {
			return $this->Mail_Parser->get_saved_mail_id();
		}
	}
}
