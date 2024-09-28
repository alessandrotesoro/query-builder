<?php // phpcs:ignore WordPress.Files.FileName
/**
 * InvalidQueryException class.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Exceptions;

use Exception;

/**
 * Exception thrown when an invalid query is encountered.
 */
class InvalidQueryException extends Exception {
	/**
	 * The default error message for this exception.
	 *
	 * @var string
	 */
	protected $message = 'Query is invalid or incomplete.';

	/**
	 * Construct the exception.
	 *
	 * @param string         $message  The Exception message to throw.
	 * @param int            $code     The Exception code.
	 * @param Exception|null $previous The previous throwable used for the exception chaining.
	 */
	public function __construct( $message = '', $code = 0, Exception $previous = null ) {
		if (empty( $message )) {
			$message = $this->message;
		}
		parent::__construct( $message, $code, $previous );
	}
}
