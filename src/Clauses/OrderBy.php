<?php // phpcs:ignore WordPress.Files.FileName
/**
 * OrderBy class.
 *
 * This class provides functionality to add ORDER BY clauses to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   GPL-3.0-or-later
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use InvalidArgumentException;
use Sematico\Baselibs\QueryBuilder\Statements\Statement;

/**
 * Class OrderBy
 *
 * Provides methods to add and manage ORDER BY clauses in SQL queries.
 */
class OrderBy extends Clause {

	/**
	 * Stores the columns and their sorting directions for the ORDER BY clause.
	 *
	 * @var string[] An array of strings, each representing a column and its sorting direction.
	 */
	protected $order = [];

	/**
	 * Add an ORDER BY clause to the query.
	 *
	 * This method allows adding a column and its sorting direction to the ORDER BY clause.
	 *
	 * @param string      $column    The name of the column to order by.
	 * @param string|null $direction Optional. The sorting direction, either 'ASC' or 'DESC'. Default is 'asc'.
	 *
	 * @return Statement Returns the parent Statement object for method chaining.
	 *
	 * @throws InvalidArgumentException If the provided direction is neither 'ASC' nor 'DESC'.
	 */
	public function order_by( string $column, string $direction = 'asc' ): Statement {
		$direction = strtoupper( $direction );

		if ( ! in_array( $direction, [ 'ASC', 'DESC' ], true ) ) {
			throw new InvalidArgumentException( 'Direction should be either ASC or DESC' );
		}

		$this->order[] = "$column $direction";
		return $this->statement;
	}

	/**
	 * Convert the current ORDER BY clauses to an SQL string.
	 *
	 * This method generates the SQL string for the ORDER BY clause based on the
	 * columns and directions stored in the $order property.
	 *
	 * @return string The generated SQL string for the ORDER BY clause.
	 */
	public function to_sql(): string {
		if ( empty( $this->order ) ) {
			return '';
		}

		return 'ORDER BY ' . implode( ', ', $this->order );
	}

	/**
	 * Check if the OrderBy clause is empty.
	 *
	 * @return bool
	 */
	public function is_empty(): bool {
		return empty( $this->order );
	}
}
