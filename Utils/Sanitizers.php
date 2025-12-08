<?php
namespace MJ\Whitebox\Utils;

use MJ\Whitebox\Utils;

class Sanitizers extends Utils {
	/**
	 * Normalize and validate Iranian phone numbers.
	 *
	 * Returns:
	 * - normalized number (string) if valid
	 * - empty string "" if invalid
	 *
	 * @param string $string
	 * @return string
	 */
	public static function phone( $string ) {
		$string = Utils::convert_chars( $string );
		// 1) Clean non-digits & convert Persian digits
		$raw = preg_replace('/[^\d]/u', '', $string);

		// 2) Remove country code
		$raw = preg_replace('/^(98|0098)/', '', $raw);

		// 3) Add leading zero if appropriate
		if (strlen($raw) >= 9 && $raw[0] !== '0') {
			$raw = '0' . $raw;
		}

		// 4) Validate mobile (11 digits, starts with 09)
		if (preg_match('/^09\d{9}$/', $raw)) {
			return $raw;
		}

		// 5) Validate landline (۳ رقمی کد + شماره ۷ یا ۸ رقمی)
		// پیش‌شماره‌های رسمی: 021 تا 091 (بازه‌های معتبر)
		if (preg_match('/^0(11|13|17|21|25|26|28|31|34|35|38|41|44|45|51|54|56|58|61|66|71|74|76|77|81|83|84|86|87)\d{7,8}$/', $raw)) {
			return $raw;
		}

		// 6) Validate short service numbers (3–5 digits)
		if (preg_match('/^\d{3,5}$/', $raw)) {
			return $raw;
		}

		// 7) Validate extension (3–6 digits)
		if (preg_match('/^\d{3,6}$/', $raw)) {
			return $raw;
		}

		// Invalid
		return "";
	}

	/**
	 * Sanitize OTP
	 *
	 * @param string $string
	 * @param integer $length
	 * @return integer
	 */
	public static function otp( $string, $length = 4 ) {
		$string = parent::convert_chars( $string );
		preg_match_all( '/\d+/', $string, $matches );
		$string = absint( implode( "", $matches[0] ) );
		$string = substr( $string, 0, $length );
		return $string;
	}

	/**
	 * Sanitize and normalize a price value.
	 *
	 * Converts characters, removes non-numeric characters, and returns
	 * the price as an integer if possible, otherwise as a float.
	 *
	 * @param string|int|float $price The price value to process.
	 * @param bool $empty_to_zero Optional. If false, empty strings remain empty. Default true.
	 *
	 * @return int|float|string Sanitized price as int or float, or empty string if applicable.
	 */
	public static function price( $price, $empty_to_zero = true ) {
		if( !$empty_to_zero && $price === '' ) return '';

		$price = parent::convert_chars( $price );
		if( !is_numeric( $price ) ) {
			$price = preg_replace( "/[^0-9.]/", "", $price );
		}

		return absint( $price ) == $price ? absint( $price ) : floatval( $price );
	}

	/**
	 * Sanitize IP
	 *
	 * @param string $string
	 * @return string IP
	 */
	public static function ip( $string ) {
		$string = parent::convert_chars( $string );
		return filter_var( $string, FILTER_VALIDATE_IP ) ? $string : '';
	}

	/**
	 * Ensure a string is a valid HTML tag from custom tags.
	 *
	 * Converts characters and validates against allowed custom tags.
	 * Defaults to 'div' if the value is not allowed.
	 *
	 * @param string $string The input string to validate as a tag.
	 *
	 * @return string Validated HTML tag.
	 */
	public static function tag( $string ) : string {
		return parent::ensure_values_in_array( parent::convert_chars( $string ), array_keys( parent::custom_tags() ), 'div' );
	}

	/**
	 * Format and validate a credit card number.
	 *
	 * Removes spaces and ensures the number is exactly 16 characters.
	 * Returns an empty string if the length is less than 16.
	 *
	 * @param string $string The input card number.
	 *
	 * @return string Formatted 16-digit card number or empty string.
	 */
	public static function card_number( string $string ) : string {
		$string = parent::convert_chars( $string );
		$string = str_replace( " ", "", $string );
		if( strlen( $string ) > 16 ) {
			$string = substr( $string, 0, 16 );
		} else if( strlen( $string ) < 16 ) {
			$string = "";
		}
		return $string;
	}

	/**
	 * Format and validate an IBAN (Shaba) number.
	 *
	 * Removes spaces, converts characters, and extracts the last 24 digits.
	 * Returns an empty string if no valid number is found.
	 *
	 * @param string $shaba The input Shaba/IBAN number.
	 *
	 * @return string Formatted 24-digit Shaba number or empty string.
	 */
	public static function shaba_number( string $shaba ) {
		$shaba = parent::convert_chars( $shaba );
		$shaba = str_replace( " ", "", $shaba );
		preg_match('/\d{24}$/', $shaba, $shaba);
		return $shaba[0] ?? '';
	}
}