<?php // phpcs:ignore WordPress.Files.FileName
/**
 * Join trait.
 *
 * This trait provides functionality to add JOIN clauses to SQL queries.
 *
 * @package   Sematico\Baselibs\QueryBuilder
 * @author    Alessandro Tesoro <alessandro.tesoro@icloud.com>
 * @copyright 2024 Sematico
 * @license   MIT
 */

namespace Sematico\Baselibs\QueryBuilder\Clauses;

use InvalidArgumentException;

/**
 * Class Join
 *
 * Provides methods to add and manage JOIN clauses in SQL queries.
 */
class Join extends Clause {

	/**
	 * @var array Array of JOIN clauses.
	 */
	protected $joins = [];

	/**
	 * Add an INNER JOIN clause to the query.
	 *
	 * @param string $table    The table to join.
	 * @param string $alias    The alias for the joined table.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition.
	 * @param string $second   The second column for the join condition.
	 * @return self Fluent interface.
	 */
	public function join( string $table, string $alias, string $first, string $operator, string $second ): self {
		return $this->add_join( 'INNER JOIN', $table, $alias, $first, $operator, $second );
	}

	/**
	 * Add a LEFT JOIN clause to the query.
	 *
	 * @param string $table    The table to join.
	 * @param string $alias    The alias for the joined table.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition.
	 * @param string $second   The second column for the join condition.
	 * @return self Fluent interface.
	 */
	public function left_join( string $table, string $alias, string $first, string $operator, string $second ): self {
		return $this->add_join( 'LEFT JOIN', $table, $alias, $first, $operator, $second );
	}

	/**
	 * Add a RIGHT JOIN clause to the query.
	 *
	 * @param string $table    The table to join.
	 * @param string $alias    The alias for the joined table.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition.
	 * @param string $second   The second column for the join condition.
	 * @return self Fluent interface.
	 */
	public function right_join( string $table, string $alias, string $first, string $operator, string $second ): self {
		return $this->add_join( 'RIGHT JOIN', $table, $alias, $first, $operator, $second );
	}

	/**
	 * Add a JOIN clause to the query.
	 *
	 * @param string $type     The type of join (INNER JOIN, LEFT JOIN, RIGHT JOIN).
	 * @param string $table    The table to join.
	 * @param string $alias    The alias for the joined table.
	 * @param string $first    The first column for the join condition.
	 * @param string $operator The operator for the join condition.
	 * @param string $second   The second column for the join condition.
	 * @return self Fluent interface.
	 * @throws InvalidArgumentException If the join type is invalid.
	 */
	protected function add_join( string $type, string $table, string $alias, string $first, string $operator, string $second ): self {
		$allowed_types = [ 'INNER JOIN', 'LEFT JOIN', 'RIGHT JOIN' ];
		if ( ! in_array( $type, $allowed_types, true )) {
			throw new InvalidArgumentException( 'Invalid join type' );
		}

		global $wpdb;
		$prefixed_table = $wpdb->prefix . $table;

		$this->joins[] = [
			'type'     => $type,
			'table'    => $prefixed_table,
			'alias'    => $alias,
			'first'    => $first,
			'operator' => $operator,
			'second'   => $second,
		];

		$this->set_param( 'joins', $this->joins );

		return $this;
	}

	/**
	 * Check if the join clause is empty by looking at
	 * the "joins" array.
	 *
	 * @return bool True if the join clause is empty, false otherwise.
	 */
	public function is_empty(): bool {
		return empty( $this->joins );
	}

	/**
	 * Convert the current JOIN clauses into an SQL string.
	 *
	 * @return string The generated SQL JOIN clauses.
	 */
	public function to_sql(): string {
		if ($this->is_empty()) {
			return '';
		}

		$sql = [];
		foreach ($this->get_params()['joins'] as $join) {
			$sql[] = "{$join['type']} {$join['table']} AS {$join['alias']} ON {$join['first']} {$join['operator']} {$join['second']}";
		}

		return implode( ' ', $sql );
	}
}
