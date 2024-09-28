<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Utils class that contains various helper functions.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder;

/**
 * Utility class for various helper functions.
 */
class Utils {
	/**
	 * Verify if the given value is an associative array.
	 *
	 * @param mixed $subject The subject to check.
	 * @return bool true if the given value is an associative array, false otherwise
	 */
	public static function is_associative_array( $subject ): bool {
		if ( ! is_array( $subject )) {
			return false;
		}

		foreach ( array_keys( $subject ) as $key) {
			if ( ! is_string( $key )) {
				return false;
			}
		}

		return true;
	}
}
