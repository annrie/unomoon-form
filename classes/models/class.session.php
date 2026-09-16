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
 * Unomoon_Form_Session
 */
class Unomoon_Form_Session {

	/**
	 * Session name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * @var string
	 */
	protected $session_id;

	/**
	 * Transient's survival time.
	 *
	 * @var int
	 */
	protected $expiration = 1440;

	/**
	 * Prefix of the transient key. Keeps session storage inside a namespace
	 * of its own, so a forged cookie can never address another transient.
	 */
	const TRANSIENT_PREFIX = 'unomoonform_session_';

	/**
	 * Constructor.
	 *
	 * @param string $name Session name.
	 */
	public function __construct( $name ) {
		$this->name = Unomoon_Form_Config::NAME . '_session_' . $name;

		$session_id = $this->_get_session_id_from_cookie();
		if ( null === $session_id ) {
			$session_id = sha1( wp_create_nonce( $this->name ) . ip2long( $this->get_remote_addr() ) . uniqid() );
			$secure     = apply_filters( 'unomoonform_secure_cookie', is_ssl() );
			// setcookie() only warns when headers are already sent, so guard instead of swallowing the warning (same as Unomoon_Form_CSRF::save_token()).
			if ( ! headers_sent() ) {
				setcookie(
					$this->name,
					$session_id,
					array(
						'expires'  => 0,
						'path'     => COOKIEPATH,
						'domain'   => COOKIE_DOMAIN,
						'secure'   => $secure,
						'httponly' => true,
						'samesite' => 'Lax',
					)
				);
			}
		}

		$this->session_id = $session_id;
	}

	/**
	 * Return the session ID stored in the cookie, or null when it is missing or malformed.
	 *
	 * The ID is always a 40 character hex string (sha1), so anything else is rejected
	 * before it can be used as part of a transient key.
	 *
	 * @return string|null
	 */
	protected function _get_session_id_from_cookie() {
		if ( ! isset( $_COOKIE[ $this->name ] ) ) {
			return null;
		}

		$session_id = sanitize_text_field( wp_unslash( $_COOKIE[ $this->name ] ) );
		if ( ! preg_match( '/\A[a-f0-9]{40}\z/', $session_id ) ) {
			return null;
		}

		return $session_id;
	}

	/**
	 * Return the transient key for this session.
	 *
	 * @return string
	 */
	protected function _transient_key() {
		return self::TRANSIENT_PREFIX . $this->session_id;
	}

	/**
	 * Save values.
	 *
	 * @param array $data Saving session data.
	 */
	public function save( array $data ) {
		$transient_data = get_transient( $this->_transient_key() );
		if ( ! is_array( $transient_data ) ) {
			$transient_data = array();
		}

		foreach ( $data as $key => $value ) {
			$transient_data[ $key ] = $value;
		}
		set_transient( $this->_transient_key(), $transient_data, $this->expiration );
	}

	/**
	 * Save a value.
	 *
	 * @param string $key   Session value name.
	 * @param mixed  $value Session value.
	 */
	public function set( $key, $value ) {
		$transient_data = get_transient( $this->_transient_key() );
		if ( ! is_array( $transient_data ) ) {
			$transient_data = array();
		}

		$transient_data[ $key ] = $value;
		set_transient( $this->_transient_key(), $transient_data, $this->expiration );
	}

	/**
	 * Push a value.
	 *
	 * @param string $key   Session value name.
	 * @param mixed  $value Session value.
	 */
	public function push( $key, $value ) {
		$transient_data = get_transient( $this->_transient_key() );
		if ( ! is_array( $transient_data ) ) {
			$transient_data = array();
		}

		if ( ! isset( $transient_data[ $key ] ) ) {
			$transient_data[ $key ] = array( $value );
		} else {
			if ( is_array( $transient_data[ $key ] ) ) {
				$transient_data[ $key ][] = $value;
			} else {
				$transient_data[ $key ]   = array( $transient_data[ $key ] );
				$transient_data[ $key ][] = $value;
			}
		}
		set_transient( $this->_transient_key(), $transient_data, $this->expiration );
	}

	/**
	 * Return a value.
	 *
	 * @param string $key Session value name.
	 * @return mixed
	 */
	public function get( $key ) {
		$transient_data = get_transient( $this->_transient_key() );
		if ( is_array( $transient_data ) && isset( $transient_data[ $key ] ) ) {
			return $transient_data[ $key ];
		}
	}

	/**
	 * Return all values.
	 *
	 * @return array
	 */
	public function gets() {
		$transient_data = get_transient( $this->_transient_key() );
		if ( is_array( $transient_data ) ) {
			return $transient_data;
		}
		return array();
	}

	/**
	 * Clear a value.
	 *
	 * @param string $key Session value name.
	 */
	public function clear_value( $key ) {
		$transient_data = get_transient( $this->_transient_key() );
		if ( is_array( $transient_data ) && isset( $transient_data[ $key ] ) ) {
			unset( $transient_data[ $key ] );
			set_transient( $this->_transient_key(), $transient_data, $this->expiration );
		}
	}

	/**
	 * Clear values.
	 */
	public function clear_values() {
		delete_transient( $this->_transient_key() );
	}

	/**
	 * Return $_SERVER['REMOTE_ADDR'].
	 *
	 * @return string
	 */
	protected function get_remote_addr() {
		if ( isset( $_SERVER['REMOTE_ADDR'] ) ) {
			$remote_addr = sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) );
			if ( filter_var( $remote_addr, FILTER_VALIDATE_IP ) ) {
				return $remote_addr;
			}
		}
		return '127.0.0.1';
	}
}
