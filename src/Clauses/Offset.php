<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Offset class.
 *
 * This class provides functionality to add OFFSET clauses to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use Sematico\Baselibs\QueryBuilder\Statements\Statement;

/**
 * Class Offset
 *
 * Provides methods to set and convert OFFSET clauses for SQL queries.
 */
class Offset extends Clause {

	/**
	 * The number of rows to skip before starting to return rows.
	 *
	 * @var int|null $offset The OFFSET value, or null if not set.
	 */
	private $offset = null;

	/**
	 * Add the OFFSET of rows to skip.
	 *
	 * @param int $offset The number of rows to skip.
	 * @return Statement Returns the parent Statement object for method chaining.
	 */
	public function offset( int $offset ): Statement {
		$this->offset = $offset;
		return $this->statement;
	}

	/**
	 * Convert the current OFFSET to an SQL string.
	 *
	 * @return string The generated SQL OFFSET clause.
	 */
	public function to_sql(): string {
		if ( $this->is_empty() ) {
			return '';
		}
		return $this->prepare_sql( "OFFSET $this->offset" );
	}

	/**
	 * Check if the Offset clause is empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return $this->offset === null || $this->offset < 0;
	}
}
