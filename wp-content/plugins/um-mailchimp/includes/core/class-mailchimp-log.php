<?php
namespace um_ext\um_mailchimp\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Mailchimp_Log {

	private $file;
	private $file_path;

	function __construct() {
		$this->file_path = UM()->files()->upload_basedir . 'mailchimp.log';
		$this->file = fopen( $this->file_path, 'ab+');
	}

	function get() {
		$size = filesize( $this->file_path );
		$content = $size ? fread( $this->file, $size ) : '';
		return $content;
	}

	function add( $data ) {
		$content = date('m/d/Y H:i:s');

		if( !empty( $data['method'] ) ) {
			$content .= ' [' .  strtoupper( $data['method'] ) . ']';
		}

		if( !empty( $data['url'] ) ) {
			$content .= ' ' . $data['url'];
		}

		if( isset( $data['status'] ) ) {
			$content .= ' -> ' . ( $data['status'] ? 'success': 'error' );
		}

		if( isset( $data['args'] ) ) {
			$content .= PHP_EOL;
			$content .= json_encode( $data['args'] );
		}

		if( isset( $data['response'] ) ) {
			$content .= PHP_EOL;
			$content .= json_encode( $data['response'] );
		}

		if( isset( $data['trace'] ) && defined('UM_DEBUG') ) {
			$content .= PHP_EOL;
			$content .= $data['trace'];
		}

		$content .= PHP_EOL . PHP_EOL;

		fwrite( $this->file, $content );
	}

	function clear() {
		unlink( $this->file_path );
	}

}
