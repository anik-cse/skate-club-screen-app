<?php
/**
 * QR Code generation class.
 *
 * Uses chillerlan/php-qrcode library for pure PHP QR code generation.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Skate_Club_QR_Generator {

	/**
	 * Generate QR code as base64 data URI.
	 *
	 * @since    1.0.0
	 * @param    string    $url      URL to encode.
	 * @param    int       $size     QR code size in pixels (optional).
	 * @return   string    Data URI of QR code image.
	 */
	public static function generate( $url, $size = 300 ) {
		$options = new QROptions([
			'version'      => 5,
			'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
			'eccLevel'     => QRCode::ECC_L,
			'scale'        => 10,
			'imageBase64'  => true,
		]);

		$qrcode = new QRCode($options);
		return $qrcode->render($url);
	}

	/**
	 * Generate QR code as PNG and return base64.
	 *
	 * @since    1.0.0
	 * @param    string    $url      URL to encode.
	 * @param    int       $size     QR code size in pixels.
	 * @return   string    Base64 encoded PNG.
	 */
	public static function generate_base64( $url, $size = 300 ) {
		return self::generate($url, $size);
	}

	/**
	 * Generate QR code as SVG.
	 *
	 * @since    1.0.0
	 * @param    string    $url      URL to encode.
	 * @return   string    SVG markup.
	 */
	public static function generate_svg( $url ) {
		$options = new QROptions([
			'version'      => 5,
			'outputType'   => QRCode::OUTPUT_MARKUP_SVG,
			'eccLevel'     => QRCode::ECC_L,
		]);

		$qrcode = new QRCode($options);
		return $qrcode->render($url);
	}

	/**
	 * Generate QR codes for all session forms.
	 *
	 * @since    1.0.0
	 * @param    int       $session_id    Session ID.
	 * @return   array     Array of QR code data URIs.
	 */
	public static function generate_session_qr_codes( $session_id ) {
		$site_url = get_site_url();

		// Generate static URLs without session hash so QR codes remain the same across all sessions
		$forms = array(
			'song_request'  => $site_url . '/skate-club-submit-song-request/',
			'vote_songs'    => $site_url . '/skate-club-vote-songs/',
			'upload_media'  => $site_url . '/skate-club-upload-media/',
			'enter_raffle'  => $site_url . '/skate-club-enter-raffle/',
		);

		$qr_codes = array();

		foreach ( $forms as $key => $url ) {
			$qr_codes[ $key ] = self::generate( $url );
		}

		return $qr_codes;
	}

	/**
	 * Save QR code as PNG file.
	 *
	 * @since    1.0.0
	 * @param    string    $url          URL to encode.
	 * @param    string    $filename     File name to save.
	 * @param    int       $size         QR code size.
	 * @return   string|false File path or false on failure.
	 */
	public static function save_to_file( $url, $filename, $size = 300 ) {
		$options = new QROptions([
			'version'      => 5,
			'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
			'eccLevel'     => QRCode::ECC_L,
			'scale'        => 10,
			'imageBase64'  => false,
		]);

		$qrcode = new QRCode($options);

		// Get upload directory
		$upload_dir = wp_upload_dir();
		$file_path = $upload_dir['path'] . '/' . $filename . '.png';

		try {
			// Render and save
			$qrcode->render($url, $file_path);
			return $file_path;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Get QR code output directly (for inline display).
	 *
	 * @since    1.0.0
	 * @param    string    $url      URL to encode.
	 * @param    string    $type     Output type ('png' or 'svg').
	 * @return   string    QR code output.
	 */
	public static function get_qr_output( $url, $type = 'png' ) {
		if ( $type === 'svg' ) {
			return self::generate_svg( $url );
		}

		return self::generate( $url );
	}
}
