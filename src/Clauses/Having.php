<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Having class.
 *
 * This class provides functionality to add HAVING clauses to SQL queries.
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
 * Class Having
 *
 * Provides methods to add and manage HAVING clauses in SQL queries.
 */
class Having extends Clause {

	/**
	 * Array of HAVING conditions.
	 *
	 * @var array
	 */
	protected $conditions = [];

	/**
	 * Add a HAVING condition to the query.
	 *
	 * @param string $condition The HAVING condition.
	 * @param mixed  $value     The value to compare with.
	 * @param string $operator  The logical operator (AND/OR) to use. Defaults to 'AND'.
	 * @return Statement Returns the parent Statement object for method chaining.
	 * @throws InvalidArgumentException If the condition is empty or not a string.
	 */
	public function having( string $condition, $value, string $operator = 'AND' ): Statement {
		if ( empty( $condition ) || ! is_string( $condition ) ) {
			throw new InvalidArgumentException( 'HAVING condition must be a non-empty string.' );
		}

		$operator = strtoupper( $operator );
		if ( ! in_array( $operator, [ 'AND', 'OR' ], true ) ) {
			throw new InvalidArgumentException( 'Invalid operator. Must be AND or OR.' );
		}

		$this->conditions[] = [
			'condition' => $condition,
			'operator'  => $operator,
		];
		$this->set_param( $condition, $value );

		return $this->statement;
	}

	/**
	 * Add an AND HAVING condition to the query.
	 *
	 * @param string $condition The HAVING condition.
	 * @param mixed  $value     The value to compare with.
	 * @return Statement Returns the parent Statement object for method chaining.
	 */
	public function and_having( string $condition, $value ): Statement {
		return $this->having( $condition, $value, 'AND' );
	}

	/**
	 * Add an OR HAVING condition to the query.
	 *
	 * @param string $condition The HAVING condition.
	 * @param mixed  $value     The value to compare with.
	 * @return Statement Returns the parent Statement object for method chaining.
	 */
	public function or_having( string $condition, $value ): Statement {
		return $this->having( $condition, $value, 'OR' );
	}

	/**
	 * Convert the current HAVING conditions into an SQL string.
	 *
	 * @return string The generated SQL HAVING clause.
	 */
	public function to_sql(): string {
		if ( empty( $this->conditions ) ) {
			return '';
		}

		$sql_parts = [];
		foreach ( $this->conditions as $index => $condition ) {
			if ( $index === 0 ) {
				$sql_parts[] = $condition['condition'];
			} else {
				$sql_parts[] = $condition['operator'] . ' ' . $condition['condition'];
			}
		}

		$sql = implode( ' ', $sql_parts );
		return 'HAVING ' . $this->prepare_sql( $sql );
	}

	/**
	 * Check if the having clause is empty.
	 *
	 * @return bool True if the having clause is empty, false otherwise.
	 */
	public function is_empty(): bool {
		return empty( $this->conditions );
	}

	/**
	 * Return the parent Statement object to allow chaining.
	 *
	 * @return Statement
	 */
	public function end(): Statement {
		return $this->statement;
	}
}
