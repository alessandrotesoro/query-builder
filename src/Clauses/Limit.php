<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Limit class.
 *
 * This class provides functionality to add LIMIT clauses to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use Sematico\Baselibs\QueryBuilder\Statements\Statement;

/**
 * Class Limit
 *
 * Provides methods to set and convert LIMIT clauses for SQL queries.
 */
class Limit extends Clause {

	/**
	 * The maximum number of rows to return.
	 *
	 * @var int|null $limit The LIMIT value, or null if not set.
	 */
	private $limit = null;

	/**
	 * Add the LIMIT of rows to select.
	 *
	 * @param int $limit The maximum number of rows to return.
	 * @return Statement Returns the parent Statement object for method chaining.
	 */
	public function limit( int $limit ): Statement {
		$this->limit = $limit;
		return $this->statement;
	}

	/**
	 * Convert the current LIMIT to an SQL string.
	 *
	 * @return string The generated SQL LIMIT clause.
	 */
	public function to_sql(): string {
		if ( $this->is_empty() ) {
			return '';
		}
		return "LIMIT $this->limit";
	}

	/**
	 * Check if the Limit clause is empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return $this->limit === null || $this->limit < 0;
	}
}
